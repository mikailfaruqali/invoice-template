package main

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
)

func DetectChromeBinary(overridePath string) (string, error) {
	if overridePath != "" {
		if _, err := os.Stat(overridePath); err == nil {
			return overridePath, nil
		}
		if p, err := exec.LookPath(overridePath); err == nil {
			return p, nil
		}
		return "", fmt.Errorf("specified Chrome binary not found at '%s'", overridePath)
	}

	var candidates []string

	if runtime.GOOS == "windows" {
		localAppData := os.Getenv("LOCALAPPDATA")
		programFiles := os.Getenv("ProgramFiles")
		programFilesX86 := os.Getenv("ProgramFiles(x86)")
		systemDrive := os.Getenv("SystemDrive")
		if systemDrive == "" {
			systemDrive = "C:"
		}

		candidates = []string{
			filepath.Join(programFiles, "Google", "Chrome", "Application", "chrome.exe"),
			filepath.Join(programFilesX86, "Google", "Chrome", "Application", "chrome.exe"),
			filepath.Join(localAppData, "Google", "Chrome", "Application", "chrome.exe"),
			filepath.Join(systemDrive, `\Program Files\Google\Chrome\Application\chrome.exe`),
			filepath.Join(systemDrive, `\Program Files (x86)\Google\Chrome\Application\chrome.exe`),

			filepath.Join(programFiles, "Microsoft", "Edge", "Application", "msedge.exe"),
			filepath.Join(programFilesX86, "Microsoft", "Edge", "Application", "msedge.exe"),
			filepath.Join(localAppData, "Microsoft", "Edge", "Application", "msedge.exe"),

			filepath.Join(programFiles, "BraveSoftware", "Brave-Browser", "Application", "brave.exe"),
			filepath.Join(localAppData, "BraveSoftware", "Brave-Browser", "Application", "brave.exe"),
			filepath.Join(programFiles, "Chromium", "Application", "chrome.exe"),
			filepath.Join(localAppData, "Chromium", "Application", "chrome.exe"),
		}

		for _, name := range []string{"chrome.exe", "msedge.exe", "chromium.exe", "brave.exe"} {
			if p, err := exec.LookPath(name); err == nil {
				candidates = append([]string{p}, candidates...)
			}
		}
	} else if runtime.GOOS == "darwin" {
		candidates = []string{
			"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome",
			"/Applications/Chromium.app/Contents/MacOS/Chromium",
			"/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge",
			"/Applications/Brave Browser.app/Contents/MacOS/Brave Browser",
		}
		for _, name := range []string{"google-chrome", "chromium", "chromium-browser"} {
			if p, err := exec.LookPath(name); err == nil {
				candidates = append([]string{p}, candidates...)
			}
		}
	} else {
		candidates = []string{
			"/usr/bin/google-chrome-stable",
			"/usr/bin/google-chrome",
			"/usr/bin/chromium-browser",
			"/usr/bin/chromium",
			"/snap/bin/chromium",
			"/usr/bin/microsoft-edge-stable",
			"/usr/bin/microsoft-edge",
			"/usr/bin/brave-browser",
		}
		for _, name := range []string{"google-chrome-stable", "google-chrome", "chromium-browser", "chromium", "microsoft-edge", "brave-browser"} {
			if p, err := exec.LookPath(name); err == nil {
				candidates = append([]string{p}, candidates...)
			}
		}
	}

	for _, path := range candidates {
		if path == "" {
			continue
		}
		if info, err := os.Stat(path); err == nil && !info.IsDir() {
			return path, nil
		}
	}

	return "", fmt.Errorf("could not auto-detect Chrome or Chromium executable. Set it with the --chrome flag")
}
