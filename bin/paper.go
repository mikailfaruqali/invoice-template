package main

import (
	"fmt"
	"strconv"
	"strings"
)

var paperSizes = map[string][2]float64{
	"A3":     {11.69, 16.54},
	"A4":     {8.27, 11.69},
	"A5":     {5.83, 8.27},
	"LETTER": {8.5, 11.0},
	"LEGAL":  {8.5, 14.0},
}

func GetPaperDimensions(paper string, orientation string) (float64, float64, error) {
	size, ok := paperSizes[strings.ToUpper(strings.TrimSpace(paper))]
	if !ok {
		return 0, 0, fmt.Errorf("unsupported page size: %s (supported: A3, A4, A5, Letter, Legal)", paper)
	}

	width, height := size[0], size[1]
	if isLandscape(orientation) {
		width, height = height, width
	}
	return width, height, nil
}

var unitsToInches = []struct {
	suffix  string
	divisor float64
}{
	{"mm", 25.4},
	{"cm", 2.54},
	{"in", 1.0},
	{"pt", 72.0},
	{"px", 96.0},
}

func ParseDimensionToInches(dimStr string) (float64, error) {
	lower := strings.ToLower(strings.TrimSpace(dimStr))
	if lower == "" {
		return 0, nil
	}

	for _, u := range unitsToInches {
		if strings.HasSuffix(lower, u.suffix) {
			val, err := strconv.ParseFloat(strings.TrimSpace(strings.TrimSuffix(lower, u.suffix)), 64)
			if err != nil {
				return 0, fmt.Errorf("invalid dimension value '%s': %w", dimStr, err)
			}
			return val / u.divisor, nil
		}
	}

	val, err := strconv.ParseFloat(lower, 64)
	if err != nil {
		return 0, fmt.Errorf("invalid dimension value '%s' (expected a number with an optional mm/cm/in/pt/px unit)", dimStr)
	}
	return val / 25.4, nil
}

func InchesToPoints(in float64) float64 { return in * 72.0 }

func isLandscape(orientation string) bool {
	return strings.EqualFold(strings.TrimSpace(orientation), "landscape")
}
