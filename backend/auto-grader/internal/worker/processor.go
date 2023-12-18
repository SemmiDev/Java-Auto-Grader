package worker

import (
	"context"
	"github.com/SemmiDev/auto-grader/internal"
	classRepository "github.com/SemmiDev/auto-grader/internal/domain/class/repository"
	submissionRepository "github.com/SemmiDev/auto-grader/internal/domain/submission/repository"
	"github.com/SemmiDev/auto-grader/internal/grader"
	"github.com/hibiken/asynq"
	"github.com/redis/go-redis/v9"
	"github.com/rs/zerolog/log"
)

const (
	QueueCritical = "critical"
	QueueDefault  = "default"
)

type RedisTaskProcessor struct {
	server               *asynq.Server
	classRepository      *classRepository.ClassRepository
	submissionRepository *submissionRepository.SubmissionRepository
	config               *internal.Config
	grader               *grader.Grader
}

func NewRedisTaskProcessor(
	redisOpt asynq.RedisClientOpt,
	classRepository *classRepository.ClassRepository,
	submissionRepository *submissionRepository.SubmissionRepository,
	config *internal.Config,
) *RedisTaskProcessor {
	logger := NewLogger()

	redis.SetLogger(logger)

	server := asynq.NewServer(
		redisOpt,
		asynq.Config{
			Concurrency: 5,
			Queues: map[string]int{
				QueueCritical: 5,
				QueueDefault:  0,
			},
			ErrorHandler: asynq.ErrorHandlerFunc(func(ctx context.Context, task *asynq.Task, err error) {
				log.Error().Err(err).Str("type", task.Type()).Msg("process task failed")
			}),
			Logger: logger,
		},
	)

	return &RedisTaskProcessor{
		server:               server,
		classRepository:      classRepository,
		submissionRepository: submissionRepository,
		config:               config,
		grader:               grader.NewGrader(config),
	}
}

func (processor *RedisTaskProcessor) Start() error {
	mux := asynq.NewServeMux()
	mux.HandleFunc(TaskGradeJavaSubmission, processor.ProcessTaskGradeJavaSubmission)
	return processor.server.Start(mux)
}
