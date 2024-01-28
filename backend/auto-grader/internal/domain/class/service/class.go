package service

import (
	"context"
	"errors"
	"time"

	"github.com/SemmiDev/auto-grader/internal"
	"github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	"github.com/SemmiDev/auto-grader/internal/domain/class/model"
	"github.com/SemmiDev/auto-grader/internal/domain/class/repository"
	userRepository "github.com/SemmiDev/auto-grader/internal/domain/user/repository"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"go.mongodb.org/mongo-driver/mongo"
)

var (
	ErrClassNotFound  = errors.New("Kelas tidak ditemukan")
	ErrHasJoinedClass = errors.New("Anda sudah bergabung dengan kelas ini")
	ErrUserNotFound   = errors.New("Pengguna tidak ditemukan")
)

type ClassService struct {
	classRepository *repository.ClassRepository
	userRepository  *userRepository.UserRepository
	config          *internal.Config
}

func NewClassService(classRepository *repository.ClassRepository, userRepository *userRepository.UserRepository, config *internal.Config) *ClassService {
	return &ClassService{classRepository: classRepository, userRepository: userRepository, config: config}
}

type IClassService interface {
	GetAllClassesByUser(ctx context.Context, userID primitive.ObjectID) ([]*model.GetClassesReadModel, error)
	GetClassByID(ctx context.Context, classID primitive.ObjectID) (*domain.Class, error)
	GetClassDetailsReadModel(ctx context.Context, classID, userID primitive.ObjectID) (*model.GetClassDetailsReadModel, error)
	CreateNewClass(ctx context.Context, OwnerID primitive.ObjectID, req *model.CreateNewClassDTO) error
	DeleteClass(ctx context.Context, classID primitive.ObjectID) error
	UpdateClassDetails(ctx context.Context, ClassID primitive.ObjectID, req *model.UpdateClassDTO) error
	AddMember(ctx context.Context, req *model.AddMemberClassDTO) error
	RemoveMember(ctx context.Context, classID, userID primitive.ObjectID) error
}

func (c *ClassService) GetAllClassesByUser(ctx context.Context, userID primitive.ObjectID) ([]*model.GetClassesReadModel, error) {
	classes, err := c.classRepository.GetAllClassesByUser(ctx, userID)

	result := make([]*model.GetClassesReadModel, 0, len(classes))

	for _, class := range classes {
		owner, err := c.userRepository.GetUserByID(ctx, class.Owner)
		if err != nil {
			return nil, err
		}

		role, err := class.CheckAndGetRole(userID)
		if err != nil {
			return nil, err
		}

		data := model.GetClassesReadModel{
			Class: class,
			Owner: owner,
			Role:  role,
		}

		result = append(result, &data)
	}

	return result, err
}

func (c *ClassService) GetClassByID(ctx context.Context, classID primitive.ObjectID) (*domain.Class, error) {
	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return nil, ErrClassNotFound
		}
		return nil, err
	}

	return class, nil
}

func (c *ClassService) GetClassDetailsReadModel(ctx context.Context, classID, userID primitive.ObjectID) (*model.GetClassDetailsReadModel, error) {
	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return nil, ErrClassNotFound
		}
		return nil, err
	}

	if len(class.Assignments) > 0 {
		for i, assignment := range class.Assignments {
			dueDate := assignment.DueDate
			class.Assignments[i].DueDate = dueDate.In(time.FixedZone("WIB", 7*60*60))
			class.Assignments[i].SetIsPastDeadline()
		}
	}

	owner, err := c.userRepository.GetUserByID(ctx, class.Owner)
	if err != nil {
		return nil, err
	}

	teachers, err := c.userRepository.GetUsersByID(ctx, class.Teachers)
	if err != nil {
		return nil, err
	}

	students, err := c.userRepository.GetUsersByID(ctx, class.Students)
	if err != nil {
		return nil, err
	}

	role, err := class.CheckAndGetRole(userID)
	if err != nil {
		return nil, err
	}

	permissions := domain.Permissions[role]

	return &model.GetClassDetailsReadModel{
		Class:       class,
		Owner:       owner,
		Teachers:    teachers,
		Students:    students,
		Role:        role,
		Permissions: permissions,
	}, nil
}

func (c *ClassService) CreateNewClass(ctx context.Context, OwnerID primitive.ObjectID, req *model.CreateNewClassDTO) error {
	classCode, err := helper.GenerateUniqueClassCode(7)

	class, err := domain.NewClass(req.Name, req.Description, classCode, OwnerID)
	if err != nil {
		return err
	}

	return c.classRepository.Save(ctx, class)
}

func (c *ClassService) DeleteClass(ctx context.Context, classID primitive.ObjectID) error {
	return c.classRepository.DeleteByID(ctx, classID)
}

func (c *ClassService) UpdateClassDetails(ctx context.Context, ClassID primitive.ObjectID, req *model.UpdateClassDTO) error {
	class, err := c.classRepository.GetByID(ctx, ClassID)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return ErrClassNotFound
		}
		return err
	}

	if err := class.ChangeName(req.Name); err != nil {
		return err
	}

	class.ChangeDescription(req.Description)

	if err := c.classRepository.Save(ctx, class); err != nil {
		return err
	}

	return nil
}

func (c *ClassService) AddMember(ctx context.Context, req *model.AddMemberClassDTO) error {
	class, err := c.classRepository.GetByCode(ctx, req.Code)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return ErrClassNotFound
		}
		return err
	}

	// join using class code
	if req.Email == "" {
		if class.HasJoinedClass(req.UserID) {
			return ErrHasJoinedClass
		}
	} else {
		if userData, err := c.userRepository.GetUserByEmail(ctx, req.Email); err != nil {
			if errors.Is(err, mongo.ErrNoDocuments) {
				return ErrUserNotFound
			}
			return err
		} else {
			if class.HasJoinedClass(userData.ID) {
				return ErrHasJoinedClass
			}
			req.UserID = userData.ID
		}
	}

	class.AddMember(req.UserID, req.Role)

	err = c.classRepository.Save(ctx, class)
	return err
}

func (c *ClassService) RemoveMember(ctx context.Context, classID, userID primitive.ObjectID) error {
	class, err := c.classRepository.GetByID(ctx, classID)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return ErrClassNotFound
		}
		return err
	}

	if err := class.RemoveMember(userID); err != nil {
		return err
	}

	return c.classRepository.Save(ctx, class)
}
