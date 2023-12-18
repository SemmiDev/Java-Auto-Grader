package helper

import (
	gonanoid "github.com/matoous/go-nanoid/v2"
)

func GenerateUniqueClassCode(size int) (string, error) {
	const charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
	return gonanoid.Generate(charset, size)
}
