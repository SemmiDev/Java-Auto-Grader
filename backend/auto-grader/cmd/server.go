package main

import (
	"encoding/json"
	"github.com/SemmiDev/auto-grader/internal"
	classService "github.com/SemmiDev/auto-grader/internal/domain/class/service"
	submissionService "github.com/SemmiDev/auto-grader/internal/domain/submission/service"
	userService "github.com/SemmiDev/auto-grader/internal/domain/user/service"
	"github.com/SemmiDev/auto-grader/internal/helper"
	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	"github.com/go-chi/cors"
	"github.com/rs/zerolog/log"
	"net/http"
)

type Server struct {
	router        *chi.Mux
	token         *helper.PasetoMaker
	config        *internal.Config
	userSvc       *userService.UserService
	classSvc      *classService.ClassService
	submissionSvc *submissionService.SubmissionService
}

func NewServer(
	token *helper.PasetoMaker,
	config *internal.Config,
	userSvc *userService.UserService,
	classSvc *classService.ClassService,
	submissionSvc *submissionService.SubmissionService) *Server {

	r := chi.NewRouter()

	s := &Server{
		router:        r,
		token:         token,
		config:        config,
		userSvc:       userSvc,
		classSvc:      classSvc,
		submissionSvc: submissionSvc,
	}

	return s
}

func (s *Server) SetupMiddlewares() {
	s.router.Use(cors.Handler(cors.Options{
		AllowedOrigins:   []string{"https://*", "http://*"},
		AllowedMethods:   []string{"GET", "POST", "PUT", "DELETE", "OPTIONS"},
		AllowedHeaders:   []string{"Accept", "Authorization", "Content-Type", "X-CSRF-Token"},
		ExposedHeaders:   []string{"Link"},
		AllowCredentials: true,
		MaxAge:           86400 * 30,
	}))

	s.router.Use(middleware.Logger)
	s.router.Use(middleware.Recoverer)
}

func (s *Server) SetupRoutes() {
	s.router.Post("/api/login", s.LoginHandler)

	s.router.Route("/api", func(r chi.Router) {
		r.Use(s.AuthMiddleware)

		r.Get("/classes", s.GetClassesHandler)
		r.Post("/classes", s.CreateNewClassHandler)
		r.Post("/classes/join", s.JoinClassMemberHandler)
		r.Get("/classes/{classId}", s.GetClassDetailsHandler)
		r.Put("/classes/{classId}", s.UpdateClassHandler)
		r.Delete("/classes/{classId}", s.DeleteClassHandler)
		r.Delete("/classes/{classId}/members/{userId}", s.RemoveClassMemberHandler)

		r.Post("/classes/{classId}/assignments", s.CreateAssignmentHandler)
		r.Get("/classes/{classId}/assignments/{assignmentId}", s.GetAssignmentDetailsHandler)
		r.Put("/classes/{classId}/assignments/{assignmentId}", s.UpdateAssignmentsHandler)
		r.Get("/classes/{classId}/assignments/{assignmentId}/csv", s.GetCSVGradingSummaryHandler)
		r.Delete("/classes/{classId}/assignments/{assignmentId}", s.DeleteAssignmentHandler)

		r.Get("/classes/{classId}/assignments/{assignmentId}/leaderboard", s.GetAssignmentLeaderboardHandler)
		r.Get("/classes/{classId}/assignments/{assignmentId}/submissions", s.GetStudentSubmissionsHandler)
		r.Get("/classes/{classId}/assignments/{assignmentId}/students/{studentId}/submissions", s.GetByStudentIDSubmissionsHandler)
		r.Post("/classes/{classId}/assignments/{assignmentId}/submissions", s.CreateSubmissionsHandler)

		r.Get("/assignments/teachers/templates/download", s.DownloadAssignmentTemplateHandler)
		r.Get("/assignments/students/templates/download", s.DownloadAssignmentStudentTemplateHandler)
		r.Get("/logs/{logFile}", s.GetStudentSubmissionLogHandler)
	})
}

func (s *Server) Start(addr string) error {
	log.Info().Msgf("HTTP server started on %s", addr)
	return http.ListenAndServe(addr, s.router)
}

func SuccessResponse(w http.ResponseWriter, code int, data interface{}) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(code)

	resp := map[string]any{
		"success": true,
		"data":    data,
	}

	json.NewEncoder(w).Encode(resp)
}

func ErrorResponse(w http.ResponseWriter, code int, message string) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(code)

	resp := map[string]any{
		"success": false,
		"message": message,
	}

	json.NewEncoder(w).Encode(resp)
}
