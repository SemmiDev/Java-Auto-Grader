package domain

import (
	"errors"
	"time"

	"strings"

	"github.com/SemmiDev/auto-grader/internal/helper"
	"go.mongodb.org/mongo-driver/bson/primitive"
)

type Class struct {
	ID          primitive.ObjectID `json:"id" bson:"_id"`
	Name        string             `json:"name" bson:"name"`
	Description string             `json:"description" bson:"description"`
	Code        string             `json:"code" bson:"code"`

	Owner       primitive.ObjectID   `json:"owner" bson:"owner"`
	Teachers    []primitive.ObjectID `json:"teachers" bson:"teachers"`
	Students    []primitive.ObjectID `json:"students" bson:"students"`
	Assignments []*Assignment        `json:"assignments" bson:"assignments"`

	CreatedAt time.Time `json:"created_at" bson:"created_at"`
	UpdatedAt time.Time `json:"updated_at" bson:"updated_at"`
}

var (
	ErrClassNameEmpty = errors.New("Nama kelas tidak boleh kosong")
	ErrClassCodeEmpty = errors.New("Kode kelas tidak boleh kosong")
	ErrUserNotFound   = errors.New("User tidak ditemukan")
)

func NewClass(name, description, code string, owner primitive.ObjectID) (*Class, error) {
	if strings.TrimSpace(name) == "" {
		return nil, ErrClassNameEmpty
	}

	if strings.TrimSpace(code) == "" {
		return nil, ErrClassCodeEmpty
	}

	return &Class{
		ID:          primitive.NewObjectID(),
		Name:        name,
		Description: description,
		Code:        code,

		Owner:       owner,
		Teachers:    make([]primitive.ObjectID, 0),
		Students:    make([]primitive.ObjectID, 0),
		Assignments: make([]*Assignment, 0),

		CreatedAt: helper.NewTime(),
		UpdatedAt: helper.NewTime(),
	}, nil
}

func (c *Class) ChangeName(name string) error {
	if strings.TrimSpace(name) == "" {
		return ErrClassNameEmpty
	}

	c.Name = name
	c.UpdatedAt = helper.NewTime()

	return nil
}

func (c *Class) ChangeDescription(description string) {
	c.Description = description
	c.UpdatedAt = helper.NewTime()
}

func (c *Class) HasJoinedClass(userID primitive.ObjectID) bool {
	if c.Owner == userID {
		return true
	}

	for _, teacher := range c.Teachers {
		if teacher == userID {
			return true
		}
	}

	for _, student := range c.Students {
		if student == userID {
			return true
		}
	}

	return false
}

func (c *Class) AddMember(userID primitive.ObjectID, role Role) {
	if role == RoleTeacher {
		c.Teachers = append(c.Teachers, userID)
	} else {
		c.Students = append(c.Students, userID)
	}

	c.UpdatedAt = helper.NewTime()
}

func (c *Class) RemoveMember(memberID primitive.ObjectID) error {
	for i, teacher := range c.Teachers {
		if teacher == memberID {
			c.Teachers = append(c.Teachers[:i], c.Teachers[i+1:]...)
			c.UpdatedAt = helper.NewTime()
			return nil
		}
	}

	for i, student := range c.Students {
		if student == memberID {
			c.Students = append(c.Students[:i], c.Students[i+1:]...)
			c.UpdatedAt = helper.NewTime()
			return nil
		}
	}

	return ErrUserNotFound
}

func (c *Class) AddAssignment(assignment *Assignment) {
	c.Assignments = append(c.Assignments, assignment)
	c.UpdatedAt = helper.NewTime()
}

func (c *Class) CheckAndGetRole(userID primitive.ObjectID) (Role, error) {
	if c.Owner == userID {
		return RoleOwner, nil
	}

	for _, teacher := range c.Teachers {
		if teacher == userID {
			return RoleTeacher, nil
		}
	}

	for _, student := range c.Students {
		if student == userID {
			return RoleStudent, nil
		}
	}

	return "", ErrUserNotFound
}

func (c *Class) ChangeAssignment(assignmentID primitive.ObjectID, assignment *Assignment) error {
	for i, existingAssignment := range c.Assignments {
		if assignmentID == existingAssignment.ID {
			c.Assignments[i].Title = assignment.Title
			c.Assignments[i].Description = assignment.Description
			c.Assignments[i].DueDate = assignment.DueDate

			if assignment.TemplateName != "" {
				c.Assignments[i].TemplateName = assignment.TemplateName
				c.Assignments[i].TotalTestCases = assignment.TotalTestCases
			}

			c.UpdatedAt = helper.NewTime()
			return nil
		}
	}

	return ErrAssignmentNotFound
}

func (c *Class) GetAssignmentDetails(assignmentID primitive.ObjectID) (*Assignment, error) {
	for _, assignment := range c.Assignments {
		if assignment.ID == assignmentID {
			return assignment, nil
		}
	}

	return nil, ErrAssignmentNotFound
}

func (c *Class) DeleteAssignment(assignmentID primitive.ObjectID) error {
	for i, assignment := range c.Assignments {
		if assignment.ID == assignmentID {
			c.Assignments = append(c.Assignments[:i], c.Assignments[i+1:]...)
			c.UpdatedAt = helper.NewTime()
			return nil
		}
	}

	return ErrAssignmentNotFound
}
