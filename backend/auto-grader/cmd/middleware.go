package main

import (
	"context"
	"errors"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"net/http"
	"strings"
)

func (s *Server) AuthMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		authHeader := r.Header.Get("Authorization")
		if authHeader == "" || !strings.HasPrefix(authHeader, "Bearer ") {
			ErrorResponse(w, http.StatusUnauthorized, "unauthorized")
			return
		}

		token := strings.TrimPrefix(authHeader, "Bearer ")

		payload, err := s.token.VerifyToken(token)
		if err != nil {
			if errors.Is(err, helper.ErrExpiredToken) {
				ErrorResponse(w, http.StatusUnauthorized, "Silahkan login kembali")
				return
			}
			ErrorResponse(w, http.StatusUnauthorized, "unauthorized")
			return
		}

		ctx := context.WithValue(r.Context(), "user", payload)
		r = r.WithContext(ctx)

		next.ServeHTTP(w, r)
	})
}
