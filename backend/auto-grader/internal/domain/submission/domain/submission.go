package domain

import (
	"fmt"
	"os"
	"path/filepath"
	"time"

	"github.com/SemmiDev/auto-grader/internal/helper"
	"go.mongodb.org/mongo-driver/bson/primitive"
)

type SubmissionStatus string

var (
	SubmissionStatusBeingGraded        SubmissionStatus = "BEING GRADED"
	SubmissionStatusSuccessfullyGraded SubmissionStatus = "SUCCESSFULLY GRADED"
	SubmissionStatusFailedToGrade      SubmissionStatus = "FAILED TO GRADE"
)

type Submission struct {
	ID           primitive.ObjectID `json:"id" bson:"_id"`
	AssignmentID primitive.ObjectID `json:"assignment_id" bson:"assignment_id"`
	StudentID    primitive.ObjectID `json:"student_id" bson:"student_id"`

	FilePath  string           `json:"file_path" bson:"file_path"`
	TestCases TestCase         `json:"test_cases" bson:"test_cases"`
	Grade     float64          `json:"grade" bson:"grade"`
	Comment   string           `json:"comment" bson:"comment"`
	Status    SubmissionStatus `json:"status" bson:"status"`
	Logs      string           `json:"logs" bson:"logs"`
	CreatedAt time.Time        `json:"created_at" bson:"created_at"`
	UpdatedAt time.Time        `json:"updated_at" bson:"updated_at"`
}

func NewSubmission(studentID, assignmentID primitive.ObjectID, filePath string) *Submission {
	return &Submission{
		ID:           primitive.NewObjectID(),
		AssignmentID: assignmentID,
		StudentID:    studentID,
		FilePath:     filePath,
		TestCases:    NewTestCase(),
		Status:       SubmissionStatusBeingGraded,
		CreatedAt:    helper.NewTime(),
		UpdatedAt:    helper.NewTime(),
	}
}

func (s *Submission) ChangeStatus(status SubmissionStatus) {
	s.Status = status
}

func (s *Submission) ChangeTestCases(newTestCase TestCase) {
	s.TestCases = newTestCase
}

func (s *Submission) CalculateGrade(totalTestCases int64) {
	if totalTestCases == 0 || s.TestCases.Passed == 0 {
		s.Grade = 0
		return
	}

	s.Grade = (float64(s.TestCases.Passed) * 100) / float64(totalTestCases)
}

type TestCase struct {
	Passed   int64 `json:"passed" bson:"passed"`
	Failures int64 `json:"failures" bson:"failures"`
	Errors   int64 `json:"errors" bson:"errors"`
	Skipped  int64 `json:"skipped" bson:"skipped"`
}

func NewTestCase() TestCase {
	return TestCase{
		Passed:   0,
		Failures: 0,
		Errors:   0,
		Skipped:  0,
	}
}

func (s *Submission) SaveLogToFileStorage(storagePath string, logText string) error {
	fileName := fmt.Sprintf("%s.txt", s.ID.Hex())
	filePath := filepath.Join(storagePath, "logs", fileName)

	err := os.MkdirAll(filepath.Dir(filePath), os.ModePerm)
	if err != nil {
		return err
	}

	file, err := os.Create(filePath)
	if err != nil {
		return err

	}
	defer file.Close()

	_, err = file.WriteString(logText)
	if err != nil {
		return err

	}

	s.Logs = fileName
	return nil
}
