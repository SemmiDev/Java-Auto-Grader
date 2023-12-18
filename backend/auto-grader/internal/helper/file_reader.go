package helper

import (
	"archive/tar"
	"bytes"
	"errors"
	"io"
	"os"
	"strings"
)

func ReadFileContent(filePath, fileName string) ([]byte, error) {
	file, err := os.Open(filePath)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	tr := tar.NewReader(file)

	for {
		header, err := tr.Next()
		if err == io.EOF {
			break
		}

		if strings.HasSuffix(header.Name, fileName) {
			if header.Typeflag == tar.TypeDir {
				return nil, errors.New("file is a directory")
			}

			var buf bytes.Buffer
			_, err := io.Copy(&buf, tr)
			if err != nil {
				return nil, err
			}

			return buf.Bytes(), nil
		}
	}

	return nil, os.ErrNotExist
}
