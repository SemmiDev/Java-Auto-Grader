package main

import (
	"encoding/json"
	"errors"
	"github.com/SemmiDev/auto-grader/internal/domain/class/model"
	classService "github.com/SemmiDev/auto-grader/internal/domain/class/service"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/go-chi/chi/v5"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"net/http"
)

func (s *Server) GetClassesHandler(w http.ResponseWriter, r *http.Request) {
	payload := r.Context().Value("user").(*helper.Payload)

	classes, err := s.classSvc.GetAllClassesByUser(r.Context(), payload.User.ID)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	data := map[string]any{"classes": classes}
	SuccessResponse(w, http.StatusOK, data)
}

func (s *Server) CreateNewClassHandler(w http.ResponseWriter, r *http.Request) {
	var req model.CreateNewClassDTO

	decoder := json.NewDecoder(r.Body)
	if err := decoder.Decode(&req); err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	payload := r.Context().Value("user").(*helper.Payload)

	err := s.classSvc.CreateNewClass(r.Context(), payload.User.ID, &req)
	if err != nil {
		if errors.Is(err, classService.ErrUserNotFound) {
			ErrorResponse(w, http.StatusNotFound, err.Error())
			return
		}
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusCreated, nil)
}

func (s *Server) GetClassDetailsHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	payload := r.Context().Value("user").(*helper.Payload)

	classDetails, err := s.classSvc.GetClassDetailsReadModel(r.Context(), classId, payload.User.ID)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, classDetails)
}

func (s *Server) JoinClassMemberHandler(w http.ResponseWriter, r *http.Request) {
	var req model.AddMemberClassDTO

	decoder := json.NewDecoder(r.Body)
	if err := decoder.Decode(&req); err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	payload := r.Context().Value("user").(*helper.Payload)
	req.UserID = payload.User.ID

	err := s.classSvc.AddMember(r.Context(), &req)
	if err != nil {
		if errors.Is(err, classService.ErrClassNotFound) {
			ErrorResponse(w, http.StatusNotFound, err.Error())
			return
		}
		if errors.Is(err, classService.ErrUserNotFound) {
			ErrorResponse(w, http.StatusNotFound, err.Error())
			return
		}
		if errors.Is(err, classService.ErrHasJoinedClass) {
			ErrorResponse(w, http.StatusConflict, err.Error())
			return
		}

		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, nil)
}

func (s *Server) UpdateClassHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	var req model.UpdateClassDTO
	decoder := json.NewDecoder(r.Body)
	if err := decoder.Decode(&req); err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	err = s.classSvc.UpdateClassDetails(r.Context(), classId, &req)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, nil)
}

func (s *Server) DeleteClassHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	err = s.classSvc.DeleteClass(r.Context(), classId)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, nil)
}

func (s *Server) RemoveClassMemberHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	userId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "userId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	err = s.classSvc.RemoveMember(r.Context(), classId, userId)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, nil)
}
