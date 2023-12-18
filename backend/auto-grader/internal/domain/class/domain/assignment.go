package domain

import (
	"errors"
	"strings"
	"time"

	"github.com/SemmiDev/auto-grader/internal/helper"
	"go.mongodb.org/mongo-driver/bson/primitive"
)

type Assignment struct {
	ID             primitive.ObjectID `json:"id" bson:"_id"`
	CreatorID      primitive.ObjectID `json:"creator_id" bson:"creator_id"`
	Title          string             `json:"title" bson:"title"`
	Description    string             `json:"description" bson:"description"`
	TemplateName   string             `json:"template_name" bson:"template_name"`
	IsOverDue      bool               `json:"is_over_due" bson:"-"`
	TotalTestCases int64              `json:"total_test_cases" bson:"total_test_cases"`
	DueDate        time.Time          `json:"due_date" bson:"due_date"`
	CreatedAt      time.Time          `json:"created_at" bson:"created_at"`
	UpdatedAt      time.Time          `json:"updated_at" bson:"updated_at"`
}

var (
	ErrAssignmentTitleEmpty           = errors.New("Judul tugas tidak boleh kosong")
	ErrAssignmentTemplateNameEmpty    = errors.New("Nama template tidak boleh kosong")
	ErrAssignmentDueDateInPast        = errors.New("Tugas sudah lewat deadline")
	ErrAssignmentTotalTestCasesIsZero = errors.New("Jumlah test cases tidak boleh 0")
	ErrAssignmentNotFound             = errors.New("Tugas tidak ditemukan")
)

func NewAssignment(
	id, creatorID primitive.ObjectID,
	title, description, templateName string,
	totalTestCases int64,
	dueDate time.Time,
) (*Assignment, error) {

	if strings.TrimSpace(title) == "" {
		return nil, ErrAssignmentTitleEmpty
	}

	if strings.TrimSpace(templateName) == "" {
		return nil, ErrAssignmentTemplateNameEmpty
	}

	if totalTestCases <= 0 {
		return nil, ErrAssignmentTotalTestCasesIsZero
	}

	if helper.NewTime().After(dueDate) {
		return nil, ErrAssignmentDueDateInPast
	}

	return &Assignment{
		ID:             id,
		Title:          title,
		Description:    description,
		TemplateName:   templateName,
		TotalTestCases: totalTestCases,
		DueDate:        dueDate,
		CreatorID:      creatorID,
		CreatedAt:      helper.NewTime(),
		UpdatedAt:      helper.NewTime(),
	}, nil
}

func (a *Assignment) SetIsPastDeadline() {
	a.IsOverDue = helper.NewTime().After(a.DueDate)
}

func (a *Assignment) ChangeTitle(title string) error {
	if strings.TrimSpace(title) == "" {
		return ErrAssignmentTitleEmpty
	}

	a.Title = title
	return nil
}

func (a *Assignment) ChangeDescription(description string) error {
	a.Description = description
	return nil
}

func (a *Assignment) ChangeTemplateName(templateName string) error {
	if strings.TrimSpace(templateName) == "" {
		return ErrAssignmentTemplateNameEmpty
	}

	a.TemplateName = templateName
	return nil
}

func (a *Assignment) ChangeTotalTestCases(totalTestCases int64) error {
	if totalTestCases <= 0 {
		return ErrAssignmentTotalTestCasesIsZero
	}

	a.TotalTestCases = totalTestCases
	return nil
}

func (a *Assignment) ChangeDueDate(dueDate time.Time) error {
	if helper.NewTime().After(dueDate) {
		return ErrAssignmentDueDateInPast
	}

	a.DueDate = dueDate
	return nil
}
