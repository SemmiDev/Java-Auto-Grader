package main

import (
	"encoding/json"
	"github.com/SemmiDev/auto-grader/internal/domain/user/model"
	"net/http"
)

func (s *Server) LoginHandler(w http.ResponseWriter, r *http.Request) {
	var req model.CreateUserRequest

	decoder := json.NewDecoder(r.Body)
	if err := decoder.Decode(&req); err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	userData, authToken, err := s.userSvc.CreateToken(r.Context(), &req)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	data := map[string]any{"user": userData, "auth_token": authToken}
	SuccessResponse(w, http.StatusOK, data)
}
