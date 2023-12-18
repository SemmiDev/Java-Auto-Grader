package service

import (
	"context"
	"github.com/SemmiDev/auto-grader/internal/domain/user/domain"
	"github.com/SemmiDev/auto-grader/internal/domain/user/model"
	"github.com/SemmiDev/auto-grader/internal/helper"
)

func (u *UserService) CreateToken(ctx context.Context, req *model.CreateUserRequest) (*domain.User, string, error) {
	var token string

	user, err := domain.NewUser(req.Name, req.Email, req.Picture)
	if err != nil {
		return nil, token, err
	}

	user, err = u.repository.Save(ctx, user)
	if err != nil {
		return nil, token, err
	}

	userPayload := helper.UserTokenPayload{
		ID:    user.ID,
		Name:  user.Name,
		Email: user.Email,
	}

	token, _, err = u.tokenMaker.CreateToken(userPayload, u.config.TokenDuration)
	if err != nil {
		return nil, token, err
	}

	return user, token, nil
}
