package service

import (
	"github.com/SemmiDev/auto-grader/internal"
	classRepository "github.com/SemmiDev/auto-grader/internal/domain/class/repository"
	"github.com/SemmiDev/auto-grader/internal/domain/submission/repository"
	"github.com/SemmiDev/auto-grader/internal/worker"
)

type SubmissionService struct {
	repository      *repository.SubmissionRepository
	classRepository *classRepository.ClassRepository
	taskDistributor *worker.RedisTaskDistributor
	config          *internal.Config
}

func NewSubmissionService(
	repository *repository.SubmissionRepository,
	classRepository *classRepository.ClassRepository,
	taskDistributor *worker.RedisTaskDistributor,
	config *internal.Config) *SubmissionService {

	return &SubmissionService{
		repository:      repository,
		classRepository: classRepository,
		taskDistributor: taskDistributor,
		config:          config,
	}
}
