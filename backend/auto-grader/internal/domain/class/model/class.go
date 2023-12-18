package model

import (
	"github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	userDomain "github.com/SemmiDev/auto-grader/internal/domain/user/domain"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"mime/multipart"
)

type CreateNewAssignmentDTO struct {
	Title       string         `json:"title"`
	Description string         `json:"description"`
	Deadline    string         `json:"deadline"`
	Template    multipart.File `json:"template"`
}

type UpdateAssignmentDTO struct {
	Title       string         `json:"title"`
	Description string         `json:"description"`
	Deadline    string         `json:"deadline"`
	Template    multipart.File `json:"template"`
}

type GetAssignmentDetailsReadModel struct {
	Assignment  *domain.Assignment  `json:"assignment"`
	Students    []*userDomain.User  `json:"students"`
	Role        domain.Role         `json:"role"`
	Permissions []domain.Permission `json:"permissions"`
}

type GetClassDetailsReadModel struct {
	Class       *domain.Class       `json:"class" bson:"class"`
	Owner       *userDomain.User    `json:"owner" bson:"owner"`
	Teachers    []*userDomain.User  `json:"teachers" bson:"teachers"`
	Students    []*userDomain.User  `json:"students" bson:"students"`
	Role        domain.Role         `json:"role" bson:"role"`
	Permissions []domain.Permission `json:"permissions"`
}

type GetClassesReadModel struct {
	Class *domain.Class    `json:"class" bson:"class"`
	Owner *userDomain.User `json:"owner" bson:"owner"`
	Role  domain.Role      `json:"role" bson:"role"`
}

type CreateNewClassDTO struct {
	Name        string `json:"name"`
	Description string `json:"description"`
}

type UpdateClassDTO struct {
	Name        string `json:"name"`
	Description string `json:"description"`
}

type AddMemberClassDTO struct {
	// when join to a class with a email
	Role  domain.Role `json:"role"`
	Email string      `json:"email"`

	// when join to a class with class code
	Code   string             `json:"code"`
	UserID primitive.ObjectID `json:"user_id"`
}
