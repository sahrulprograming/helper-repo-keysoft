package main

import (
	"crypto/aes"
	"crypto/cipher"
	"encoding/base64"
	"encoding/hex"
	"errors"
	"fmt"
	"os"
	"strings"
)

const (
	defaultPrefix = "enc1"
	defaultCipher = "AES-256-GCM"
)

func main() {
	key := os.Getenv("GENERAL_CIPHER_KEY")
	prefix := getEnv("GENERAL_CIPHER_PREFIX", defaultPrefix)
	cipherName := getEnv("GENERAL_CIPHER_CIPHER", defaultCipher)
	payload := "enc1:REPLACE_IV_BASE64:REPLACE_CIPHERTEXT_BASE64:REPLACE_TAG_BASE64"

	plainText, err := DecryptGeneralCipher(payload, key, prefix, cipherName)
	if err != nil {
		panic(err)
	}

	fmt.Println(plainText)
}

func DecryptGeneralCipher(payload, configuredKey, prefix, configuredCipher string) (string, error) {
	if payload == "" {
		return payload, nil
	}

	if !strings.HasPrefix(payload, prefix+":") {
		return payload, nil
	}

	parts := strings.SplitN(payload, ":", 4)
	if len(parts) != 4 {
		return "", errors.New("invalid encrypted payload format")
	}

	cipherName, err := resolveCipher(configuredCipher)
	if err != nil {
		return "", err
	}

	key, err := resolveKey(configuredKey)
	if err != nil {
		return "", err
	}

	nonce, err := base64.StdEncoding.DecodeString(parts[1])
	if err != nil {
		return "", fmt.Errorf("invalid nonce: %w", err)
	}

	cipherText, err := base64.StdEncoding.DecodeString(parts[2])
	if err != nil {
		return "", fmt.Errorf("invalid ciphertext: %w", err)
	}

	tag, err := base64.StdEncoding.DecodeString(parts[3])
	if err != nil {
		return "", fmt.Errorf("invalid tag: %w", err)
	}

	block, err := aes.NewCipher(key)
	if err != nil {
		return "", fmt.Errorf("create cipher: %w", err)
	}

	gcm, err := newGCM(block, cipherName)
	if err != nil {
		return "", err
	}

	if len(nonce) != gcm.NonceSize() {
		return "", fmt.Errorf("invalid nonce length: got %d want %d", len(nonce), gcm.NonceSize())
	}

	if len(tag) != gcm.Overhead() {
		return "", fmt.Errorf("invalid tag length: got %d want %d", len(tag), gcm.Overhead())
	}

	combined := make([]byte, 0, len(cipherText)+len(tag))
	combined = append(combined, cipherText...)
	combined = append(combined, tag...)

	plainText, err := gcm.Open(nil, nonce, combined, nil)
	if err != nil {
		return "", fmt.Errorf("decrypt failed: %w", err)
	}

	return string(plainText), nil
}

func newGCM(block cipher.Block, cipherName string) (cipher.AEAD, error) {
	if cipherName != defaultCipher {
		return nil, fmt.Errorf("unsupported cipher: %s", cipherName)
	}

	gcm, err := cipher.NewGCM(block)
	if err != nil {
		return nil, fmt.Errorf("create gcm: %w", err)
	}

	return gcm, nil
}

func resolveCipher(configuredCipher string) (string, error) {
	configuredCipher = strings.TrimSpace(configuredCipher)
	if configuredCipher == "" {
		return defaultCipher, nil
	}

	switch strings.ToUpper(configuredCipher) {
	case defaultCipher:
		return defaultCipher, nil
	default:
		return "", errors.New("cipher must be AES-256-GCM")
	}
}

func resolveKey(configuredKey string) ([]byte, error) {
	configuredKey = strings.TrimSpace(configuredKey)
	if configuredKey == "" {
		return nil, errors.New("encryption key is required")
	}

	if strings.HasPrefix(configuredKey, "base64:") {
		decoded, err := base64.StdEncoding.DecodeString(strings.TrimPrefix(configuredKey, "base64:"))
		if err != nil {
			return nil, fmt.Errorf("invalid base64 key: %w", err)
		}

		if len(decoded) != 32 {
			return nil, fmt.Errorf("base64 key must decode to 32 bytes, got %d", len(decoded))
		}

		return decoded, nil
	}

	if len(configuredKey) == 64 {
		decoded, err := hex.DecodeString(configuredKey)
		if err == nil && len(decoded) == 32 {
			return decoded, nil
		}
	}

	return nil, errors.New("key must use format base64:... or 64-character hex")
}

func getEnv(key, fallback string) string {
	value := strings.TrimSpace(os.Getenv(key))
	if value == "" {
		return fallback
	}

	return value
}
