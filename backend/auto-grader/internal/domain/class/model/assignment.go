package model

import (
	"github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	userDomain "github.com/SemmiDev/auto-grader/internal/domain/user/domain"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"time"
)

type GetAssignmentByIDReadModel struct {
	Assignment domain.Assignment `json:"assignment"`
	Creator    userDomain.User   `json:"creator"`
}

type GetAssignmentLeaderboardReadModel struct {
	ID        primitive.ObjectID `json:"id" bson:"_id"`
	Name      string             `json:"name" bson:"name"`
	Email     string             `json:"email" bson:"email"`
	Picture   string             `json:"picture" bson:"picture"`
	Grade     float64            `json:"grade" bson:"grade"`
	CreatedAt time.Time          `json:"created_at" bson:"created_at"`
}

type GetGradingSummaryReadModel struct {
	Email string `json:"email"`
	Name  string `json:"name"`
	Grade int    `json:"grade"`
}
