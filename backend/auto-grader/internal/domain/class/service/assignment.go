package service

import (
	"archive/tar"
	"archive/zip"
	"bytes"
	"context"
	"encoding/csv"
	"fmt"
	"github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	"github.com/SemmiDev/auto-grader/internal/domain/class/model"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/rs/zerolog/log"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"io"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

func (c *ClassService) CreateNewAssignment(ctx context.Context, classID, userID primitive.ObjectID, req *model.CreateNewAssignmentDTO) error {
	parsedTime, err := helper.FormatTimeAssignment(req.Deadline)
	if err != nil {
		return err
	}

	assignmentID := primitive.NewObjectID()
	assignmentFileName := fmt.Sprintf("%s_%s.tar", classID.Hex(), assignmentID.Hex())

	file := new(bytes.Buffer)
	if _, err := io.Copy(file, req.Template); err != nil {
		log.Error().Err(err).Msg("Failed to copy template file")
		return err
	}

	zipReader, err := zip.NewReader(bytes.NewReader(file.Bytes()), int64(file.Len()))
	if err != nil {
		log.Error().Err(err).Msg("Failed to create ZIP reader")
		return err
	}

	templatePath := filepath.Join(c.config.StoragePath, "assignments", assignmentFileName)

	tarFile, err := os.Create(templatePath)
	if err != nil {
		log.Error().Err(err).Msg("Failed to create TAR file")
		return err
	}
	defer tarFile.Close()

	totalTestCases := 0
	tarWriter := tar.NewWriter(tarFile)
	defer tarWriter.Close()

	for _, file := range zipReader.File {
		zipFile, err := file.Open()
		if err != nil {
			log.Error().Err(err).Msg("Failed to open ZIP file")
			return err
		}

		fileBytes := new(bytes.Buffer)
		_, err = io.Copy(fileBytes, zipFile)
		if err != nil {
			log.Error().Err(err).Msg("Failed to read ZIP file content")
			return err
		}

		header := &tar.Header{
			Name: file.Name,
			Mode: int64(file.Mode()),
			Size: int64(fileBytes.Len()),
		}

		err = tarWriter.WriteHeader(header)
		if err != nil {
			log.Error().Err(err).Msg("Failed to write TAR header")
			return err
		}

		_, err = tarWriter.Write(fileBytes.Bytes())
		if err != nil {
			log.Error().Err(err).Msg("Failed to write TAR content")
			return err
		}

		if strings.HasSuffix(file.Name, "Test.java") {
			testFile, err := file.Open()
			if err != nil {
				log.Error().Err(err).Msg("Failed to open test file")
				return err
			}

			testFileContent, err := io.ReadAll(testFile)
			if err != nil {
				log.Error().Err(err).Msg("Failed to read test file content")
				return err
			}

			totalTestCases += strings.Count(string(testFileContent), "@Test")
		}

		if err := zipFile.Close(); err != nil {
			log.Error().Err(err).Msg("Failed to close ZIP file")
			return err
		}
	}

	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		return err
	}

	assignment, err := domain.NewAssignment(assignmentID, userID, req.Title, req.Description, assignmentFileName, int64(totalTestCases), parsedTime)
	if err != nil {
		return err
	}

	class.AddAssignment(assignment)

	return c.classRepository.Save(ctx, class)
}

func (c *ClassService) UpdateAssignment(ctx context.Context, classID, assignmentID primitive.ObjectID, req *model.UpdateAssignmentDTO) error {
	parsedTime, err := helper.FormatTimeAssignment(req.Deadline)
	if err != nil {
		return err
	}

	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		return err
	}

	assignment := domain.Assignment{
		Title:       req.Title,
		Description: req.Description,
		DueDate:     parsedTime,
	}

	assignmentFileName := fmt.Sprintf("%s_%s.tar", classID, assignmentID)

	if req.Template != nil {
		file := new(bytes.Buffer)
		if _, err := io.Copy(file, req.Template); err != nil {
			return err
		}

		zipReader, err := zip.NewReader(bytes.NewReader(file.Bytes()), int64(file.Len()))
		if err != nil {
			return err
		}

		templatePath := filepath.Join(c.config.StoragePath, "assignments", assignmentFileName)

		tarFile, err := os.Create(templatePath)
		if err != nil {
			return err
		}
		defer tarFile.Close()

		totalTestCases := 0
		tarWriter := tar.NewWriter(tarFile)
		defer tarWriter.Close()

		for _, file := range zipReader.File {
			zipFile, err := file.Open()
			if err != nil {
				return err
			}

			fileBytes := new(bytes.Buffer)
			_, err = io.Copy(fileBytes, zipFile)
			if err != nil {
				return err
			}

			header := &tar.Header{
				Name: file.Name,
				Mode: int64(file.Mode()),
				Size: int64(fileBytes.Len()),
			}

			err = tarWriter.WriteHeader(header)
			if err != nil {
				return err
			}

			_, err = tarWriter.Write(fileBytes.Bytes())
			if err != nil {
				return err
			}

			if strings.HasSuffix(file.Name, "Test.java") {
				testFile, err := file.Open()
				if err != nil {
					return err
				}

				testFileContent, err := io.ReadAll(testFile)
				if err != nil {
					return err
				}

				totalTestCases += strings.Count(string(testFileContent), "@Test")
			}

			if err := zipFile.Close(); err != nil {
				return err
			}
		}

		assignment.TemplateName = assignmentFileName
		assignment.TotalTestCases = int64(totalTestCases)
	}

	if err = class.ChangeAssignment(assignmentID, &assignment); err != nil {
		return err
	}

	if err := c.classRepository.Save(ctx, class); err != nil {
		return err
	}

	return nil
}

func (c *ClassService) DeleteAssignment(ctx context.Context, classID, assignmentID primitive.ObjectID) error {
	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		return err
	}

	assignment, err := class.GetAssignmentDetails(assignmentID)
	if err != nil {
		return err
	}

	err = class.DeleteAssignment(assignmentID)
	if err != nil {
		return err
	}

	templatePath := filepath.Join(c.config.StoragePath, "assignments", assignment.TemplateName)
	_ = os.Remove(templatePath)

	err = c.classRepository.Save(ctx, class)
	if err != nil {
		return err
	}

	return nil
}

func (c *ClassService) GetAssignmentDetails(ctx context.Context, classID, assignmentID, userID primitive.ObjectID) (*model.GetAssignmentDetailsReadModel, error) {
	var assignmentDetails model.GetAssignmentDetailsReadModel

	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		return nil, err
	}

	assignment, err := class.GetAssignmentDetails(assignmentID)
	if err != nil {
		return nil, err
	}

	assignment.DueDate = assignment.DueDate.In(time.FixedZone("WIB", 7*60*60))

	role, err := class.CheckAndGetRole(userID)
	if err != nil {
		return nil, err
	}

	if role == domain.RoleTeacher || role == domain.RoleOwner {
		students, err := c.userRepository.GetUsersByID(ctx, class.Students)
		if err != nil {
			return nil, err
		}
		assignmentDetails.Students = students
	}

	assignmentDetails.Assignment = assignment
	assignmentDetails.Role = role
	assignmentDetails.Permissions = domain.Permissions[role]

	return &assignmentDetails, nil
}

func (c *ClassService) FindAssignmentInfo(ctx context.Context, classID, assignmentID primitive.ObjectID) (*domain.Assignment, error) {
	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		return nil, err
	}

	assignment, err := class.GetAssignmentDetails(assignmentID)
	if err != nil {
		return nil, err
	}

	return assignment, nil
}

func (c *ClassService) GetAssignmentLeaderboard(ctx context.Context, assignmentID primitive.ObjectID) ([]*model.GetAssignmentLeaderboardReadModel, error) {
	return c.classRepository.GetAssignmentLeaderboard(ctx, assignmentID)
}

func (c *ClassService) GetCSVGrading(ctx context.Context, classID, assignmentID primitive.ObjectID) ([]byte, error) {
	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		return nil, err
	}

	students := class.Students

	gradingSummaryData, err := c.classRepository.GetGradingSummary(ctx, assignmentID, students)
	if err != nil {
		return nil, err
	}

	csvData, err := GenerateCSV(gradingSummaryData)
	if err != nil {
		return nil, err
	}

	return csvData, err
}

func GenerateCSV(summary []*model.GetGradingSummaryReadModel) ([]byte, error) {
	// Create a buffer to store the CSV data
	var buffer bytes.Buffer

	// Create a new CSV writer
	writer := csv.NewWriter(&buffer)

	// Write the CSV header
	header := []string{"Email", "Name", "Score"}
	err := writer.Write(header)
	if err != nil {
		return nil, err
	}

	// Write the submission data
	for _, submission := range summary {

		row := []string{
			submission.Email,
			submission.Name,
			strconv.Itoa(submission.Grade),
		}
		err := writer.Write(row)
		if err != nil {
			return nil, err
		}
	}

	// Flush the writer to ensure all data is written to the buffer
	writer.Flush()

	// Check for any errors that occurred during the writing process
	if err := writer.Error(); err != nil {
		return nil, err
	}

	// Return the CSV data as bytes
	return buffer.Bytes(), nil
}
