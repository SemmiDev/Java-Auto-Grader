package worker

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/SemmiDev/auto-grader/internal/domain/submission/domain"
	"github.com/SemmiDev/auto-grader/internal/grader"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/hibiken/asynq"
	"github.com/rs/zerolog/log"
	"time"
)

const TaskGradeJavaSubmission = "task:grade_java_submission"

func (distributor *RedisTaskDistributor) DistributeTaskGradeJavaSubmission(
	ctx context.Context,
	payload grader.GradeInput,
	opts ...asynq.Option,
) error {
	jsonPayload, err := json.Marshal(payload)
	if err != nil {
		return fmt.Errorf("failed to marshal task payload: %w", err)
	}

	task := asynq.NewTask(TaskGradeJavaSubmission, jsonPayload, opts...)
	info, err := distributor.client.EnqueueContext(ctx, task)
	if err != nil {
		return fmt.Errorf("failed to enqueue task: %w", err)
	}

	log.Info().Str("type", task.Type()).Str("queue", info.Queue).Int("max_retry", info.MaxRetry).Msg("enqueued task")

	return nil
}

func (processor *RedisTaskProcessor) ProcessTaskGradeJavaSubmission(ctx context.Context, task *asynq.Task) error {
	var payload grader.GradeInput
	if err := json.Unmarshal(task.Payload(), &payload); err != nil {
		return fmt.Errorf("failed to unmarshal payload: %w", asynq.SkipRetry)
	}

	return processor.processGrading(ctx, payload)
}

func (processor *RedisTaskProcessor) processGrading(ctx context.Context, payload grader.GradeInput) error {
	submission, err := processor.submissionRepository.GetByID(ctx, payload.SubmissionID)
	if err != nil {
		return fmt.Errorf("failed to get submission: %w", err)
	}

	log.Info().Msgf("START GRADING: %s", submission.ID.Hex())

	gradeCtx, gradeCancel := context.WithTimeout(context.Background(), time.Minute*5)
	defer gradeCancel()

	var gradeOutput *grader.GradeOutput
	done := make(chan error, 1)

	go func(payload *grader.GradeInput) {
		gradeOutput, err = processor.grader.GradeJavaSubmission(gradeCtx, payload)
		done <- err
	}(&payload)

	select {
	case <-ctx.Done():
		return processor.processGradingTimeout(ctx, &payload, submission)
	case err := <-done:
		if err != nil {
			return processor.processGradingFailure(ctx, &payload, submission, err)
		}
		return processor.processGradingSuccess(ctx, &payload, submission, gradeOutput)
	}
}

func (processor *RedisTaskProcessor) processGradingSuccess(ctx context.Context, payload *grader.GradeInput, submission *domain.Submission, gradeOutput *grader.GradeOutput) error {
	log.Info().Msgf("FINISH GRADING: %s", submission.ID.String())

	if err := submission.SaveLogToFileStorage(processor.config.StoragePath, gradeOutput.Logs); err != nil {
		return fmt.Errorf("failed to save log: %w", err)
	}

	submission.TestCases.Passed = gradeOutput.Passed
	submission.TestCases.Failures = gradeOutput.Failures
	submission.TestCases.Skipped = gradeOutput.Skipped
	submission.TestCases.Errors = gradeOutput.Errors
	submission.Status = domain.SubmissionStatusSuccessfullyGraded
	submission.UpdatedAt = helper.NewTime()
	submission.CalculateGrade(payload.TotalTestCases)

	log.Info().Msgf("grading process completed for submission: %v", submission)

	if err := processor.submissionRepository.Update(ctx, submission); err != nil {
		return fmt.Errorf("failed to update submission: %w", err)
	}

	log.Info().
		Str("type", TaskGradeJavaSubmission).
		Any("submission id", payload.SubmissionID).
		Msg("processed task")

	return nil
}

func (processor *RedisTaskProcessor) processGradingTimeout(ctx context.Context, payload *grader.GradeInput, submission *domain.Submission) error {
	log.Info().Any("assignment id", payload.AssignmentID).Msg("Grading timeout")

	submission.Status = domain.SubmissionStatusFailedToGrade
	submission.UpdatedAt = helper.NewTime()
	if err := processor.submissionRepository.Update(ctx, submission); err != nil {
		return fmt.Errorf("failed to update submission: %w", err)
	}

	return fmt.Errorf("grading timeout: %w", ctx.Err())
}

func (processor *RedisTaskProcessor) processGradingFailure(ctx context.Context, payload *grader.GradeInput, submission *domain.Submission, err error) error {
	submission.Status = domain.SubmissionStatusFailedToGrade
	submission.UpdatedAt = helper.NewTime()

	if err := processor.submissionRepository.Update(ctx, submission); err != nil {
		return fmt.Errorf("failed to update submission: %w", err)
	}

	log.Error().
		Err(err).
		Str("type", TaskGradeJavaSubmission).
		Any("submission id", payload.SubmissionID).
		Msg("failed to grade java submission")

	return fmt.Errorf("failed to grade java submission: %w", err)
}
