package helper

import (
	"errors"
	"fmt"
	"github.com/o1egl/paseto"
	"github.com/rs/zerolog/log"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"golang.org/x/crypto/chacha20poly1305"
	"time"
)

var (
	ErrInvalidToken = errors.New("token tidak valud")
	ErrExpiredToken = errors.New("token telah kadaluarsa")
)

type UserTokenPayload struct {
	ID    primitive.ObjectID `json:"id"`
	Name  string             `json:"name"`
	Email string             `json:"email"`
}

type Payload struct {
	ID        primitive.ObjectID `json:"id"`
	User      UserTokenPayload   `json:"user"`
	IssuedAt  time.Time          `json:"issued_at"`
	ExpiredAt time.Time          `json:"expired_at"`
}

func NewPayload(userTokenPayload UserTokenPayload, duration time.Duration) (*Payload, error) {
	now := NewTime()

	payload := &Payload{
		ID:        primitive.NewObjectID(),
		User:      userTokenPayload,
		IssuedAt:  now,
		ExpiredAt: now.Add(duration),
	}

	return payload, nil
}

func (payload *Payload) Valid() error {
	if NewTime().After(payload.ExpiredAt) {
		return ErrExpiredToken
	}
	return nil
}

type PasetoMaker struct {
	paseto       *paseto.V2
	symmetricKey []byte
}

func NewPasetoMaker(symmetricKey string) (*PasetoMaker, error) {
	if len(symmetricKey) != chacha20poly1305.KeySize {
		return nil, fmt.Errorf("invalid key size: must be exactly %d characters", chacha20poly1305.KeySize)
	}

	maker := &PasetoMaker{
		paseto:       paseto.NewV2(),
		symmetricKey: []byte(symmetricKey),
	}

	log.Info().Msg("Paseto Token Maker created successfully")
	return maker, nil
}

func (maker *PasetoMaker) CreateToken(userTokenPayload UserTokenPayload, duration time.Duration) (string, *Payload, error) {
	payload, err := NewPayload(userTokenPayload, duration)
	if err != nil {
		return "", payload, err
	}

	token, err := maker.paseto.Encrypt(maker.symmetricKey, payload, nil)
	return token, payload, err
}

func (maker *PasetoMaker) VerifyToken(token string) (*Payload, error) {
	payload := &Payload{}

	err := maker.paseto.Decrypt(token, maker.symmetricKey, payload, nil)
	if err != nil {
		return nil, ErrInvalidToken
	}

	err = payload.Valid()
	if err != nil {
		return nil, err
	}

	return payload, nil
}
