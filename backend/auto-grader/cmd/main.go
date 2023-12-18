package main

import (
	"github.com/SemmiDev/auto-grader/internal"
	classRepository "github.com/SemmiDev/auto-grader/internal/domain/class/repository"
	classService "github.com/SemmiDev/auto-grader/internal/domain/class/service"
	submissionRepository "github.com/SemmiDev/auto-grader/internal/domain/submission/repository"
	submissionService "github.com/SemmiDev/auto-grader/internal/domain/submission/service"
	userRepository "github.com/SemmiDev/auto-grader/internal/domain/user/repository"
	userService "github.com/SemmiDev/auto-grader/internal/domain/user/service"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/SemmiDev/auto-grader/internal/worker"
	"github.com/hibiken/asynq"
	"github.com/rs/zerolog/log"
)

func main() {
	config, err := internal.LoadConfig(".")
	if err != nil {
		log.Fatal().Msg("Failed to load configuration")
	}

	mongoDB, err := internal.NewMongoDB(config.MongodbURI)
	if err != nil {
		log.Fatal().Msg("Failed to connect to MongoDB")
	}

	defer func() {
		if err := mongoDB.Close(); err != nil {
			log.Fatal().Msg("Failed to close MongoDB connection")
		}
	}()

	token, err := helper.NewPasetoMaker(config.TokenSymmetricKey)
	if err != nil {
		log.Fatal().Msg("Failed to create Paseto token maker")
	}

	redisOpt := asynq.RedisClientOpt{Addr: config.RedisAddress}
	taskDistributor := worker.NewRedisTaskDistributor(redisOpt)

	autoGraderDB := mongoDB.GetClient().Database("auto-grader")

	userRepo := userRepository.NewUserRepository(autoGraderDB, "users")
	classRepo := classRepository.NewClassUserRepository(autoGraderDB, "classes", "users", "submissions")
	submissionRepo := submissionRepository.NewSubmissionRepository(autoGraderDB, "submissions")

	userSvc := userService.NewUserService(userRepo, token, config)
	classSvc := classService.NewClassService(classRepo, userRepo, config)
	submissionSvc := submissionService.NewSubmissionService(submissionRepo, classRepo, taskDistributor, config)

	s := NewServer(token, config, userSvc, classSvc, submissionSvc)
	s.SetupMiddlewares()
	s.SetupRoutes()

	go runTaskProcessor(redisOpt, classRepo, submissionRepo, config)

	err = s.Start(config.HTTPServerAddress)
	if err != nil {
		panic(err)
	}
}

func runTaskProcessor(
	redisOpt asynq.RedisClientOpt,
	classRepo *classRepository.ClassRepository,
	submissionRepo *submissionRepository.SubmissionRepository,
	config *internal.Config,
) {
	taskProcessor := worker.NewRedisTaskProcessor(redisOpt, classRepo, submissionRepo, config)
	log.Info().Msg("Start task processor")
	if taskProcessorErr := taskProcessor.Start(); taskProcessorErr != nil {
		log.Fatal().Err(taskProcessorErr).Msg("failed to start task processor")
	}
}
