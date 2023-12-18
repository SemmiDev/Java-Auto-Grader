package helper

import (
	"regexp"
	"strings"
)

func CleanLogs(input string) string {
	// Menghapus karakter yang tidak diinginkan
	re := regexp.MustCompile(`[^a-zA-Z0-9\s:.()-]+|-+`)
	cleaned := re.ReplaceAllString(input, "")

	// Menghapus kata "INFO" dan "ERROR"
	cleaned = strings.ReplaceAll(cleaned, "INFO", "")
	cleaned = strings.ReplaceAll(cleaned, "ERROR", "")
	cleaned = strings.ReplaceAll(cleaned, "FINISHED", "")

	// Menghapus karakter ")" dan "P"
	cleaned = strings.ReplaceAll(cleaned, ")", "")
	cleaned = strings.ReplaceAll(cleaned, "P", "")

	// Menghapus angka sebelum "Tests run"
	re = regexp.MustCompile(`\d+ Tests run:`)
	cleaned = re.ReplaceAllString(cleaned, "Tests run:")

	// Menghapus spasi kosong di sebelah kiri kalimat
	re = regexp.MustCompile(`\n\s+`)
	cleaned = re.ReplaceAllString(cleaned, "\n")

	return cleaned
}
