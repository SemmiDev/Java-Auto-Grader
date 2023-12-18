package repository

import (
	"context"
	"errors"
	"github.com/SemmiDev/auto-grader/internal/domain/submission/domain"
	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"go.mongodb.org/mongo-driver/mongo"
	"go.mongodb.org/mongo-driver/mongo/options"
)

type SubmissionRepository struct {
	collection *mongo.Collection
}

func NewSubmissionRepository(database *mongo.Database, collectionName string) *SubmissionRepository {
	return &SubmissionRepository{
		collection: database.Collection(collectionName),
	}
}

func (r *SubmissionRepository) Save(ctx context.Context, submission *domain.Submission) (*domain.Submission, error) {
	_, err := r.collection.InsertOne(ctx, submission)
	if err != nil {
		return nil, err
	}

	return submission, nil
}

func (r *SubmissionRepository) GetByID(ctx context.Context, submissionID primitive.ObjectID) (*domain.Submission, error) {
	filter := bson.M{"_id": submissionID}
	var submission domain.Submission
	err := r.collection.FindOne(ctx, filter).Decode(&submission)
	if err != nil {
		return nil, err
	}

	return &submission, nil
}

func (r *SubmissionRepository) GetsByAssignmentAndStudent(ctx context.Context, assignmentID, studentID primitive.ObjectID) ([]*domain.Submission, error) {
	filter := bson.M{"assignment_id": assignmentID, "student_id": studentID}
	opts := options.Find().SetSort(bson.D{{"created_at", -1}}) // Sort by created_at in descending order

	cursor, err := r.collection.Find(ctx, filter, opts)
	if err != nil {
		return nil, err
	}
	defer cursor.Close(ctx)

	submissions := make([]*domain.Submission, 0)
	if err := cursor.All(ctx, &submissions); err != nil {
		return nil, err
	}

	return submissions, nil
}

func (r *SubmissionRepository) Update(ctx context.Context, submission *domain.Submission) error {
	filter := bson.M{"_id": submission.ID}
	update := bson.M{
		"$set": bson.M{
			"test_cases.passed":   submission.TestCases.Passed,
			"test_cases.failures": submission.TestCases.Failures,
			"test_cases.errors":   submission.TestCases.Errors,
			"test_cases.skipped":  submission.TestCases.Skipped,
			"grade":               submission.Grade,
			"comment":             submission.Comment,
			"status":              submission.Status,
			"logs":                submission.Logs,
			"updated_at":          submission.UpdatedAt,
		},
	}

	_, err := r.collection.UpdateOne(ctx, filter, update)
	return err
}

func (r *SubmissionRepository) DeleteSubmission(ctx context.Context, submissionID primitive.ObjectID) error {
	filter := bson.M{"_id": submissionID}
	_, err := r.collection.DeleteOne(ctx, filter)
	if err != nil {
		return err
	}

	return nil
}

func (r *SubmissionRepository) GetHighestScore(ctx context.Context, assignmentID, studentID primitive.ObjectID) (*domain.Submission, error) {
	filter := bson.M{"assignment_id": assignmentID, "student_id": studentID}
	opts := options.FindOne().SetSort(bson.D{{"grade", -1}})
	var highestScoreSubmission domain.Submission
	err := r.collection.FindOne(ctx, filter, opts).Decode(&highestScoreSubmission)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return nil, nil
		}
		return nil, err
	}

	return &highestScoreSubmission, nil
}
