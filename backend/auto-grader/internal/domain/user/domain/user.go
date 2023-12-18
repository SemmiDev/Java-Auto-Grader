package domain

import (
	"errors"
	"strings"
	"time"

	"github.com/SemmiDev/auto-grader/internal/helper"
	"go.mongodb.org/mongo-driver/bson/primitive"
)

type User struct {
	ID        primitive.ObjectID `json:"id" bson:"_id"`
	Name      string             `json:"name" bson:"name"`
	Email     string             `json:"email" bson:"email"`
	Picture   string             `json:"picture" bson:"picture"`
	CreatedAt time.Time          `json:"created_at" bson:"created_at"`
	UpdatedAt time.Time          `json:"updated_at" bson:"updated_at"`
}

var (
	ErrUserNameEmpty  = errors.New("Nama tidak boleh kosong")
	ErrUserEmailEmpty = errors.New("Email tidak boleh kosong")
)

func NewUser(name, email, picture string) (*User, error) {
	if strings.TrimSpace(picture) == "" {
		picture = generateUserPicture(name)
	}

	return &User{
		ID:        primitive.NewObjectID(),
		Name:      name,
		Email:     email,
		Picture:   picture,
		CreatedAt: helper.NewTime(),
		UpdatedAt: helper.NewTime(),
	}, nil
}

func generateUserPicture(name string) string {
	var initials strings.Builder
	for _, prefix := range strings.Split(name, " ") {
		initials.WriteString(prefix[:1])
	}

	return "https://ui-avatars.com/api/?name=" + initials.String()
}
