package repository

import (
	"context"
	"errors"
	"github.com/SemmiDev/auto-grader/internal/domain/user/domain"
	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"go.mongodb.org/mongo-driver/mongo"
)

type UserRepository struct {
	collection *mongo.Collection
}

func NewUserRepository(database *mongo.Database, collectionName string) *UserRepository {
	return &UserRepository{
		collection: database.Collection(collectionName),
	}
}

func (r *UserRepository) Save(ctx context.Context, user *domain.User) (*domain.User, error) {
	existingUser, err := r.GetUserByEmail(ctx, user.Email)
	if err != nil && !errors.Is(err, mongo.ErrNoDocuments) {
		return nil, err
	}

	if existingUser != nil {
		return r.updateExistingUser(ctx, existingUser.ID, user)
	}

	if err := r.insertNewUser(ctx, user); err != nil {
		return nil, err
	}

	return user, nil
}

func (r *UserRepository) GetUserByEmail(ctx context.Context, email string) (*domain.User, error) {
	filter := bson.M{"email": email}
	var user domain.User
	err := r.collection.FindOne(ctx, filter).Decode(&user)
	if err != nil {
		return nil, err
	}

	return &user, nil
}

func (r *UserRepository) updateExistingUser(ctx context.Context, userID primitive.ObjectID, newUser *domain.User) (*domain.User, error) {
	filter := bson.M{"_id": userID}
	update := bson.M{"$set": bson.M{"name": newUser.Name, "picture": newUser.Picture, "updated_at": newUser.UpdatedAt}}
	_, err := r.collection.UpdateOne(context.TODO(), filter, update)
	if err != nil {
		return nil, err
	}

	// Get updated user
	updatedUser, err := r.GetUserByID(ctx, userID)
	if err != nil {
		return nil, err
	}

	return updatedUser, nil
}

func (r *UserRepository) GetUserByID(ctx context.Context, userID primitive.ObjectID) (*domain.User, error) {
	filter := bson.M{"_id": userID}
	var user domain.User
	err := r.collection.FindOne(ctx, filter).Decode(&user)
	if err != nil {
		if errors.Is(err, mongo.ErrNoDocuments) {
			return nil, nil
		}
		return nil, err
	}

	return &user, nil
}

func (r *UserRepository) insertNewUser(ctx context.Context, newUser *domain.User) error {
	_, err := r.collection.InsertOne(ctx, newUser)
	return err
}

func (r *UserRepository) GetUsersByID(ctx context.Context, userIDs []primitive.ObjectID) ([]*domain.User, error) {
	users := make([]*domain.User, 0)

	filter := bson.M{"_id": bson.M{"$in": userIDs}}
	cursor, err := r.collection.Find(ctx, filter)
	if err != nil {
		return nil, err
	}
	defer cursor.Close(ctx)

	err = cursor.All(ctx, &users)
	if err != nil {
		return nil, err
	}

	return users, nil
}
