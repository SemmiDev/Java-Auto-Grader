package grader

import (
	"archive/tar"
	"context"
	"fmt"
	"github.com/SemmiDev/auto-grader/internal"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strconv"
	"strings"

	"github.com/rs/zerolog/log"
)

type GradeInput struct {
	AssignmentID       primitive.ObjectID `json:"assignment_id" bson:"assignment_id"`
	StudentID          primitive.ObjectID `json:"student_id" bson:"student_id"`
	SubmissionID       primitive.ObjectID `json:"submission_id" bson:"submission_id"`
	TotalTestCases     int64              `json:"total_test_cases" bson:"total_test_cases"`
	SubmissionFileName string             `json:"submission_file_name" bson:"submission_file_name"`
	TemplateFileName   string             `json:"template_file_name" bson:"template_file_name"`
}

type GradeOutput struct {
	Passed   int64  `json:"passed" bson:"passed"`
	Failures int64  `json:"failures" bson:"failures"`
	Errors   int64  `json:"errors" bson:"errors"`
	Skipped  int64  `json:"skipped" bson:"skipped"`
	Logs     string `json:"logs" bson:"logs"`
}

type Grader struct {
	config *internal.Config
}

func NewGrader(config *internal.Config) *Grader {
	return &Grader{config: config}
}

func (g *Grader) GradeJavaSubmission(ctx context.Context, gradeInput *GradeInput) (*GradeOutput, error) {
	assignmentTarTemplateLocation := filepath.Join(g.config.StoragePath, "assignments", gradeInput.TemplateFileName)

	gradingProcessFolderName := fmt.Sprintf("grading_processing_%s", gradeInput.SubmissionID.String())
	gradingProcessFolderLocation := filepath.Join(g.config.StoragePath, "grading", gradingProcessFolderName)

	defer func() {
		if err := os.RemoveAll(gradingProcessFolderLocation); err != nil {
			log.Info().Msgf("failed to remove grading process folder: %s", gradingProcessFolderLocation)
		}
	}()

	if err := ExtractTar(assignmentTarTemplateLocation, gradingProcessFolderLocation); err != nil {
		return nil, err
	}

	submissionTarLocation := filepath.Join(g.config.StoragePath, "submissions", gradeInput.SubmissionFileName)
	submissionTargetLocation := filepath.Join(gradingProcessFolderLocation, "assignment", "src", "main", "java", "auto", "grader", "app")
	if err := ExtractTar(submissionTarLocation, submissionTargetLocation); err != nil {
		return nil, err
	}

	mavenCommands := []string{
		"clean",
		"test",
		"-Dmaven.compiler.failOnError=false",
		"-Dmaven.log.level=info",
		"-Dstyle.color=never",
	}

	cmd := exec.CommandContext(ctx, "mvn", mavenCommands...)
	cmd.Dir = filepath.Join(gradingProcessFolderLocation, "assignment")
	fmt.Println(cmd.String()) // print the command being executed

	// we don't need handle error here
	// cuz either success or failure, we sill need to parse and process the logs
	_ = cmd.Run()

	passedSum, failuresSum, errorsSum, skippedSum := 0, 0, 0, 0
	logsAggregate := &strings.Builder{}

	reportPath := filepath.Join(gradingProcessFolderLocation, "assignment", "target", "surefire-reports", "*.txt")
	files, err := filepath.Glob(reportPath)
	if err != nil {
		return nil, err
	}

	for _, file := range files {
		content, err := os.ReadFile(file)
		if err != nil {
			return nil, err
		}

		passed, failures, errors, skipped := ExtractJavaTestResults(string(content))

		passedSum += passed
		failuresSum += failures
		errorsSum += errors
		skippedSum += skipped

		logsAggregate.WriteString(string(content))
		logsAggregate.WriteString("\n\n")
	}

	return &GradeOutput{
		Passed:   int64(passedSum),
		Failures: int64(failuresSum),
		Errors:   int64(errorsSum),
		Skipped:  int64(skippedSum),
		Logs:     logsAggregate.String(),
	}, nil
}

func ExtractJavaTestResults(logText string) (int, int, int, int) {
	re := regexp.MustCompile(`Tests run: (\d+), Failures: (\d+), Errors: (\d+), Skipped: (\d+)`)
	matches := re.FindStringSubmatch(logText)

	if len(matches) == 5 {
		testsRun, _ := strconv.Atoi(matches[1])
		failures, _ := strconv.Atoi(matches[2])
		errors, _ := strconv.Atoi(matches[3])
		skipped, _ := strconv.Atoi(matches[4])

		if testsRun == 0 {
			return 0, failures, errors, skipped
		}

		success := testsRun - failures - errors - skipped
		return success, failures, errors, skipped
	}

	return 0, 0, 0, 0
}

func ExtractTar(tarFilePath, destFolder string) error {
	tarFile, err := os.Open(tarFilePath)
	if err != nil {
		return err
	}
	defer tarFile.Close()

	if err := os.MkdirAll(destFolder, 0755); err != nil {
		return err
	}

	tarReader := tar.NewReader(tarFile)

	for {
		header, err := tarReader.Next()
		if err == io.EOF {
			break
		}
		if err != nil {
			return err
		}

		filePath := filepath.Join(destFolder, header.Name)

		if header.Typeflag == tar.TypeDir {
			if err := os.MkdirAll(filePath, 0755); err != nil {
				return err
			}

			// Setelah direktori dibuat, atur izinnya menjadi 0777
			if err := os.Chmod(filePath, 0777); err != nil {
				return err
			}

			continue
		}

		file, err := os.OpenFile(filePath, os.O_CREATE|os.O_WRONLY, 0755)
		if err != nil {
			return err
		}
		defer file.Close()

		if _, err := io.Copy(file, tarReader); err != nil {
			return err
		}

		// Setelah berkas dibuat, atur izinnya menjadi 0777
		if err := os.Chmod(filePath, 0777); err != nil {
			return err
		}
	}

	return nil
}
