package main

import (
	"errors"
	"github.com/SemmiDev/auto-grader/internal/domain/class/domain"
	submissionService "github.com/SemmiDev/auto-grader/internal/domain/submission/service"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/go-chi/chi/v5"
	"github.com/rs/zerolog/log"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"net/http"
	"os"
	"path/filepath"
)

func (s *Server) CreateSubmissionsHandler(w http.ResponseWriter, r *http.Request) {
	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	err = r.ParseMultipartForm(32 << 20)
	if err != nil {
		log.Error().Err(err).Msg("Error parsing multipart form")
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	payload := r.Context().Value("user").(*helper.Payload)

	files := r.MultipartForm.File["files[]"]

	req := submissionService.CreateSubmissionDTO{
		AssignmentID:    assignmentId,
		ClassID:         classId,
		StudentID:       payload.User.ID,
		SubmissionFiles: files,
	}

	if err := s.submissionSvc.CreateSubmission(r.Context(), &req); err != nil {
		if errors.Is(err, domain.ErrAssignmentDueDateInPast) {
			ErrorResponse(w, http.StatusForbidden, err.Error())
			return
		}

		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusCreated, nil)
}

func (s *Server) GetStudentSubmissionsHandler(w http.ResponseWriter, r *http.Request) {
	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	user := r.Context().Value("user").(*helper.Payload)

	submissions, err := s.submissionSvc.GetStudentSubmissions(r.Context(), user.User.ID, assignmentId)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, submissions)
}

func (s *Server) GetByStudentIDSubmissionsHandler(w http.ResponseWriter, r *http.Request) {
	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	studentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "studentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	submissions, err := s.submissionSvc.GetStudentSubmissions(r.Context(), studentId, assignmentId)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, submissions)
}

func (s *Server) GetStudentSubmissionLogHandler(w http.ResponseWriter, r *http.Request) {
	logFile := chi.URLParam(r, "logFile")

	logFilePath := filepath.Join(s.config.StoragePath, "logs", logFile)
	content, err := os.ReadFile(logFilePath)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, map[string]interface{}{
		"content": helper.CleanLogs(string(content)),
	})
}
