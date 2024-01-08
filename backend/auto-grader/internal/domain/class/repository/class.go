package repository

import (
	"context"
	"errors"
	"fmt"
	"github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	"github.com/SemmiDev/auto-grader/internal/domain/class/model"
	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"go.mongodb.org/mongo-driver/mongo"
	"go.mongodb.org/mongo-driver/mongo/options"
)

type ClassRepository struct {
	classCollection      *mongo.Collection
	userCollection       *mongo.Collection
	submissionCollection *mongo.Collection
}

func NewClassUserRepository(database *mongo.Database, classCollectionName, userCollectionName, submissionCollection string) *ClassRepository {
	return &ClassRepository{
		classCollection:      database.Collection(classCollectionName),
		userCollection:       database.Collection(userCollectionName),
		submissionCollection: database.Collection(submissionCollection),
	}
}

type IClassRepository interface {
	Save(ctx context.Context, class *domain.Class) error
	GetByID(ctx context.Context, classID primitive.ObjectID) (*domain.Class, error)
	GetByCode(ctx context.Context, code string) (*domain.Class, error)
	GetAllClassesByUser(
		ctx context.Context,
		userID primitive.ObjectID,
	) ([]*domain.Class, error)
	DeleteByID(ctx context.Context, classID primitive.ObjectID) error
	GetAssignmentLeaderboard(
		ctx context.Context,
		assignmentID primitive.ObjectID,
	) ([]*model.GetAssignmentLeaderboardReadModel, error)
	GetGradingSummary(
		ctx context.Context,
		assignmentID primitive.ObjectID,
		students []primitive.ObjectID,
	) ([]*model.GetGradingSummaryReadModel, error)
	GetAssignment(
		ctx context.Context,
		classID, assignmentID primitive.ObjectID,
	) (*domain.Assignment, error)
}

func (c *ClassRepository) Save(ctx context.Context, class *domain.Class) error {
	session, err := c.classCollection.Database().Client().StartSession()
	if err != nil {
		return err
	}
	defer session.EndSession(ctx)

	_, err = session.WithTransaction(ctx, func(sessionContext mongo.SessionContext) (interface{}, error) {
		existingClass, err := c.GetByID(ctx, class.ID)
		if err != nil && !errors.Is(err, mongo.ErrNoDocuments) {
			return nil, err
		}

		if existingClass != nil {
			return nil, c.updateExistingClass(ctx, existingClass.ID, class)
		}

		_, err = c.classCollection.InsertOne(ctx, class)
		if err != nil {
			return nil, err
		}

		return nil, nil

	}, nil)

	if err != nil {
		return err
	}

	return nil
}

func (c *ClassRepository) updateExistingClass(ctx context.Context, classID primitive.ObjectID, newClass *domain.Class) error {
	filter := bson.M{"_id": classID}
	update := bson.M{"$set": bson.M{
		"name":        newClass.Name,
		"description": newClass.Description,
		"code":        newClass.Code,
		"owner":       newClass.Owner,
		"teachers":    newClass.Teachers,
		"students":    newClass.Students,
		"assignments": newClass.Assignments,
		"updated_at":  newClass.UpdatedAt,
	}}

	_, err := c.classCollection.UpdateOne(ctx, filter, update)
	if err != nil {
		return err
	}

	return nil
}

func (c *ClassRepository) GetByID(ctx context.Context, classID primitive.ObjectID) (*domain.Class, error) {
	var class domain.Class
	filter := bson.M{"_id": classID}
	err := c.classCollection.FindOne(ctx, filter).Decode(&class)
	if err != nil {
		return nil, err
	}

	return &class, nil
}

func (c *ClassRepository) GetByCode(ctx context.Context, code string) (*domain.Class, error) {
	var class domain.Class
	filter := bson.M{"code": code}
	err := c.classCollection.FindOne(ctx, filter).Decode(&class)
	if err != nil {
		return nil, err
	}

	return &class, nil
}

func (c *ClassRepository) GetAllClassesByUser(ctx context.Context, userID primitive.ObjectID) ([]*domain.Class, error) {
	matchStage := bson.M{
		"$match": bson.M{
			"$or": []bson.M{
				{"owner": userID},
				{"teachers": userID},
				{"students": userID},
			},
		},
	}

	sortStage := bson.M{
		"$sort": bson.M{
			"created_at": -1,
		},
	}

	pipeline := []bson.M{
		matchStage,
		sortStage,
	}

	cursor, err := c.classCollection.Aggregate(ctx, pipeline)
	if err != nil {
		return nil, err
	}

	defer cursor.Close(ctx)

	result := make([]*domain.Class, 0)
	if err := cursor.All(ctx, &result); err != nil {
		return nil, err
	}

	return result, nil
}

func (c *ClassRepository) DeleteByID(ctx context.Context, classID primitive.ObjectID) error {
	filter := bson.M{"_id": classID}
	_, err := c.classCollection.DeleteOne(ctx, filter)
	if err != nil {
		return err
	}

	return nil
}

func (c *ClassRepository) GetAssignmentLeaderboard(ctx context.Context, assignmentID primitive.ObjectID) ([]*model.GetAssignmentLeaderboardReadModel, error) {
	// get highest grade for each student
	// sort by submission time
	// sort by grade

	/*
	  80 -> jam 2
	  100 -> jam 1
	  90 -> jam 12

	  90 -> jam 12
	  100 -> jam 1
	  80 -> jam 2

	  100 -> jam 1
	  90 -> jam 12
	  80 -> jam 2
	*/

	// Define the pipeline for aggregation

	pipeline := []bson.M{
		{
			"$match": bson.M{
				"assignment_id": assignmentID,
			},
		},
		{
			"$sort": bson.M{
				"grade":      -1,
				"created_at": 1,
			},
		},
		{
			"$group": bson.M{
				"_id":            "$student_id",
				"Grade":          bson.M{"$max": "$grade"},
				"SubmissionTime": bson.M{"$first": "$created_at"},
			},
		},
		{
			"$lookup": bson.M{
				"from":         "users", // Assuming user information is stored in the "users" collection
				"localField":   "_id",
				"foreignField": "_id",
				"as":           "user",
			},
		},
		{
			"$unwind": "$user",
		},
		{
			"$project": bson.M{
				"Email":          "$user.email",
				"Name":           "$user.name",
				"Picture":        "$user.picture",
				"Grade":          "$Grade",
				"SubmissionTime": "$SubmissionTime",
			},
		},
		{
			"$sort": bson.M{
				"Grade":          -1,
				"SubmissionTime": 1,
			},
		},
	}

	// Execute aggregation pipeline
	cursor, err := c.submissionCollection.Aggregate(ctx, pipeline)
	if err != nil {
		return nil, fmt.Errorf("error executing aggregation pipeline: %v", err)
	}

	submissions := make([]*model.GetAssignmentLeaderboardReadModel, 0)
	err = cursor.All(ctx, &submissions)
	if err != nil {
		return nil, err
	}

	return submissions, nil
}

func (c *ClassRepository) GetGradingSummary(ctx context.Context, assignmentID primitive.ObjectID, students []primitive.ObjectID) ([]*model.GetGradingSummaryReadModel, error) {
	// Define the pipeline for aggregation
	pipeline := []bson.M{
		{
			"$match": bson.M{
				"assignment_id": assignmentID,
				"student_id":    bson.M{"$in": students},
			},
		},
		{
			"$group": bson.M{
				"_id":   "$student_id",
				"Grade": bson.M{"$max": "$grade"},
			},
		},
		{
			"$lookup": bson.M{
				"from":         "users", // Assuming user information is stored in the "users" collection
				"localField":   "_id",
				"foreignField": "_id",
				"as":           "user",
			},
		},
		{
			"$unwind": "$user",
		},
		{
			"$project": bson.M{
				"Email": "$user.email",
				"Name":  "$user.name",
				"Grade": "$Grade",
			},
		},
	}

	// Execute aggregation pipeline
	cursor, err := c.submissionCollection.Aggregate(ctx, pipeline)
	if err != nil {
		return nil, fmt.Errorf("error executing aggregation pipeline: %v", err)
	}
	defer cursor.Close(ctx)

	// Process aggregation results
	var gradingSummary []*model.GetGradingSummaryReadModel
	if err := cursor.All(ctx, &gradingSummary); err != nil {
		return nil, fmt.Errorf("error decoding aggregation results: %v", err)
	}

	return gradingSummary, nil
}

func (c *ClassRepository) GetAssignment(ctx context.Context, classID, assignmentID primitive.ObjectID) (*domain.Assignment, error) {
	filter := bson.M{"_id": classID, "assignments._id": assignmentID}
	opts := options.FindOne().SetProjection(bson.M{"assignments.$": 1})

	result := struct {
		Assignments []*domain.Assignment `json:"assignments" bson:"assignments"`
	}{}

	err := c.classCollection.FindOne(ctx, filter, opts).Decode(&result)
	if err != nil {
		return nil, err
	}

	if len(result.Assignments) == 0 {
		return nil, errors.New("Assignment not found in the class")
	}

	return result.Assignments[0], nil
}
