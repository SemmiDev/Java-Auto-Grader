package helper

import (
	"time"
)

func NewTime() time.Time {
	loc, _ := time.LoadLocation("Asia/Jakarta")
	return time.Now().In(loc)
}

func FormatTime(t time.Time) string {
	return t.Format(time.RFC3339)
}

func FormatTimeAssignment(deadline string) (time.Time, error) {
	layout := "2006-01-02T15:04"
	parsedTime, err := time.Parse(layout, deadline)
	if err != nil {
		return time.Time{}, err
	}

	asiaJakartaTimeZone, err := time.LoadLocation("Asia/Jakarta")
	if err != nil {
		return time.Time{}, err
	}

	// we need to subtract 7 hours from the parsedTime, because the parsedTime is in UTC
	offset := -7 * time.Hour
	parsedTime = parsedTime.In(asiaJakartaTimeZone)
	parsedTime = parsedTime.Add(offset)

	return parsedTime, nil
}
