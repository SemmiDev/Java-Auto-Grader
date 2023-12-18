package service

import (
	"github.com/SemmiDev/auto-grader/internal"
	"github.com/SemmiDev/auto-grader/internal/domain/user/repository"
	"github.com/SemmiDev/auto-grader/internal/helper"
)

type UserService struct {
	repository *repository.UserRepository
	tokenMaker *helper.PasetoMaker
	config     *internal.Config
}

func NewUserService(
	repository *repository.UserRepository,
	tokenMaker *helper.PasetoMaker,
	config *internal.Config) *UserService {

	return &UserService{
		repository: repository,
		tokenMaker: tokenMaker,
		config:     config,
	}
}
