package domain

type (
	Role       string
	Permission string
)

const (
	RoleOwner   Role = "Owner"
	RoleTeacher Role = "Teacher"
	RoleStudent Role = "Student"
)

const (
	CreateClassPermission Permission = "create:class"
	ReadClassPermission   Permission = "read:class"
	UpdateClassPermission Permission = "update:class"
	DeleteClassPermission Permission = "delete:class"

	CreateAssignmentPermission Permission = "create:assignment"
	ReadAssignmentPermission   Permission = "read:assignment"
	UpdateAssignmentPermission Permission = "update:assignment"
	DeleteAssignmentPermission Permission = "delete:assignment"

	AddClassMemberPermission    Permission = "add:member"
	DeleteClassMemberPermission Permission = "delete:member"

	CreateSubmissionPermission Permission = "create:submission"
	ReadSubmissionPermission   Permission = "read:submission"
)

var Permissions = map[Role][]Permission{
	RoleOwner: {
		CreateClassPermission,
		ReadClassPermission,
		UpdateClassPermission,
		DeleteClassPermission,
		CreateAssignmentPermission,
		ReadAssignmentPermission,
		UpdateAssignmentPermission,
		DeleteAssignmentPermission,
		AddClassMemberPermission,
		DeleteClassMemberPermission,
		ReadSubmissionPermission,
		ReadSubmissionPermission,
	},

	RoleTeacher: {
		CreateAssignmentPermission,
		ReadAssignmentPermission,
		UpdateAssignmentPermission,
		DeleteAssignmentPermission,
		ReadSubmissionPermission,
	},

	RoleStudent: {
		ReadAssignmentPermission,
		CreateSubmissionPermission,
		ReadSubmissionPermission,
	},
}
