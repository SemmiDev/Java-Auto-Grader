package service

import (
	"archive/tar"
	"context"
	"fmt"
	classDomain "github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	"github.com/SemmiDev/auto-grader/internal/domain/submission/domain"
	"github.com/SemmiDev/auto-grader/internal/grader"
	"github.com/SemmiDev/auto-grader/internal/worker"
	"github.com/hibiken/asynq"
	"github.com/pkg/errors"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"io"
	"mime/multipart"
	"os"
	"path/filepath"
	"strings"
)

type CreateSubmissionDTO struct {
	SubmissionFiles []*multipart.FileHeader `json:"submission_files"`
	AssignmentID    primitive.ObjectID      `json:"assignment_id"`
	StudentID       primitive.ObjectID      `json:"student_id"`
	ClassID         primitive.ObjectID      `json:"class_id"`
}

func (s *SubmissionService) CreateSubmission(ctx context.Context, req *CreateSubmissionDTO) error {
	assignment, err := s.classRepository.GetAssignment(ctx, req.ClassID, req.AssignmentID)
	if err != nil {
		return err
	}

	assignment.SetIsPastDeadline()
	if assignment.IsOverDue {
		return classDomain.ErrAssignmentDueDateInPast
	}

	submissionID := primitive.NewObjectID()
	submissionFileName := fmt.Sprintf("%s_%s_%s.tar", req.StudentID.Hex(), req.AssignmentID.Hex(), submissionID.Hex())

	tarPath := filepath.Join(s.config.StoragePath, "submissions", submissionFileName)
	tarFile, err := os.Create(tarPath)
	if err != nil {
		return errors.Wrap(err, "Error creating tar file")
	}
	defer tarFile.Close()

	tarWriter := tar.NewWriter(tarFile)
	defer tarWriter.Close()

	for _, file := range req.SubmissionFiles {
		if !strings.HasSuffix(file.Filename, ".java") {
			continue
		}

		src, err := file.Open()
		if err != nil {
			return err
		}
		defer src.Close()

		content, err := io.ReadAll(src)
		if err != nil {
			return err
		}

		dstPath := filepath.Join(file.Filename)

		header := &tar.Header{
			Name: dstPath,
			Mode: 0777,
			Size: int64(len(content)),
		}

		if err := tarWriter.WriteHeader(header); err != nil {
			return err
		}

		if _, err := tarWriter.Write(content); err != nil {
			return err
		}
	}

	submission := domain.NewSubmission(req.StudentID, req.AssignmentID, submissionFileName)
	submission.ID = submissionID

	_, err = s.repository.Save(ctx, submission)
	if err != nil {
		return err
	}

	taskPayload := grader.GradeInput{
		AssignmentID:       req.AssignmentID,
		StudentID:          req.StudentID,
		SubmissionID:       submission.ID,
		TotalTestCases:     assignment.TotalTestCases,
		SubmissionFileName: submissionFileName,
		TemplateFileName:   assignment.TemplateName,
	}

	opts := []asynq.Option{
		asynq.MaxRetry(3),
		asynq.Queue(worker.QueueCritical),
	}

	if taskErr := s.taskDistributor.DistributeTaskGradeJavaSubmission(ctx, taskPayload, opts...); taskErr != nil {
		return taskErr
	}

	return nil
}

type FindStudentSubmissionsDTO struct {
	Submissions  []*domain.Submission `json:"submissions"`
	HighestGrade float64              `json:"highest_grade"`
}

func newFindStudentSubmissionsDTO() *FindStudentSubmissionsDTO {
	return &FindStudentSubmissionsDTO{
		Submissions:  make([]*domain.Submission, 0),
		HighestGrade: 0,
	}
}

func (s *SubmissionService) GetStudentSubmissions(ctx context.Context, studentID primitive.ObjectID, assignmentID primitive.ObjectID) (*FindStudentSubmissionsDTO, error) {
	result := newFindStudentSubmissionsDTO()

	submissions, err := s.repository.GetsByAssignmentAndStudent(ctx, assignmentID, studentID)
	if err != nil {
		return result, err
	}

	highestGrade := 0.0
	for _, submission := range submissions {
		if submission.Grade > highestGrade {
			highestGrade = submission.Grade
		}
	}

	result.Submissions = submissions
	result.HighestGrade = highestGrade

	return result, nil
}
