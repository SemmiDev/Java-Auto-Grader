package main

import (
	"github.com/SemmiDev/auto-grader/internal/domain/class/model"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/go-chi/chi/v5"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"net/http"
	"path/filepath"
)

func (s *Server) CreateAssignmentHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	payload := r.Context().Value("user").(*helper.Payload)

	var req model.CreateNewAssignmentDTO
	err = r.ParseMultipartForm(10 << 20) // 10 MB
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	req.Title = r.FormValue("title")
	req.Description = r.FormValue("description")
	req.Deadline = r.FormValue("deadline")

	template, _, err := r.FormFile("template")
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}
	req.Template = template

	err = s.classSvc.CreateNewAssignment(r.Context(), classId, payload.User.ID, &req)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusCreated, nil)
}

func (s *Server) GetAssignmentDetailsHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	payload := r.Context().Value("user").(*helper.Payload)

	assignmentData, err := s.classSvc.GetAssignmentDetails(r.Context(), classId, assignmentId, payload.User.ID)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, assignmentData)
}

func (s *Server) UpdateAssignmentsHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	var req model.UpdateAssignmentDTO
	err = r.ParseMultipartForm(10 << 20) // 10 MB
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	req.Title = r.FormValue("title")
	req.Description = r.FormValue("description")
	req.Deadline = r.FormValue("deadline")

	template, _, err := r.FormFile("template")
	if err != nil {
		if err.Error() != "http: no such file" {
			ErrorResponse(w, http.StatusBadRequest, err.Error())
			return
		}
		// we don't need to update the template, so we just ignore the error
	}

	req.Template = template

	if err := s.classSvc.UpdateAssignment(r.Context(), classId, assignmentId, &req); err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, nil)
}

func (s *Server) DeleteAssignmentHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	if err := s.classSvc.DeleteAssignment(r.Context(), classId, assignmentId); err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusNoContent, nil)
}

func (s *Server) GetCSVGradingSummaryHandler(w http.ResponseWriter, r *http.Request) {
	classId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "classId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	csvFile, err := s.classSvc.GetCSVGrading(r.Context(), classId, assignmentId)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	w.Header().Set("Content-Type", "text/csv")
	w.Header().Set("Content-Disposition", "attachment; filename=nilai.csv")
	w.Write(csvFile)
}

func (s *Server) GetAssignmentLeaderboardHandler(w http.ResponseWriter, r *http.Request) {
	assignmentId, err := primitive.ObjectIDFromHex(chi.URLParam(r, "assignmentId"))
	if err != nil {
		ErrorResponse(w, http.StatusBadRequest, err.Error())
		return
	}

	leaderboard, err := s.classSvc.GetAssignmentLeaderboard(r.Context(), assignmentId)
	if err != nil {
		ErrorResponse(w, http.StatusInternalServerError, err.Error())
		return
	}

	SuccessResponse(w, http.StatusOK, leaderboard)
}

func (s *Server) DownloadAssignmentTemplateHandler(w http.ResponseWriter, r *http.Request) {
	path := filepath.Join(s.config.StoragePath, "templates", "template-java-assignment.tar")
	w.Header().Set("Content-Disposition", "attachment; filename=template-java-assignment.tar")
	w.Header().Set("Content-Type", "application/octet-stream")
	http.ServeFile(w, r, path)
}

func (s *Server) DownloadAssignmentStudentTemplateHandler(w http.ResponseWriter, r *http.Request) {
	path := filepath.Join(s.config.StoragePath, "templates", "template-java-student.tar")
	w.Header().Set("Content-Disposition", "attachment; filename=template-java-student.tar")
	w.Header().Set("Content-Type", "application/octet-stream")
	http.ServeFile(w, r, path)
}
