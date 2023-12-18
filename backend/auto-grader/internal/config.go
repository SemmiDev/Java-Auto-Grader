package internal

import (
	"github.com/rs/zerolog/log"
	"github.com/spf13/viper"
	"time"
)

type Config struct {
	Environment       string        `mapstructure:"ENVIRONMENT"`
	MongodbURI        string        `mapstructure:"MONGODB_URI"`
	RedisAddress      string        `mapstructure:"REDIS_ADDRESS"`
	HTTPServerAddress string        `mapstructure:"HTTP_SERVER_ADDRESS"`
	TokenDuration     time.Duration `mapstructure:"TOKEN_DURATION"`
	TokenSymmetricKey string        `mapstructure:"TOKEN_SYMMETRIC_KEY"`
	StoragePath       string        `mapstructure:"STORAGE_PATH"`
}

func LoadConfig(path string) (*Config, error) {
	viper.AddConfigPath(path)
	viper.SetConfigName("app")
	viper.SetConfigType("env")

	// AutomaticEnv makes Viper check if environment variables match any of the existing keys
	// (config, default or flags). If matching env vars are found, they are loaded into Viper.
	viper.AutomaticEnv()

	// ReadInConfig reads the config file from disk and unmarshal it into a map.
	if err := viper.ReadInConfig(); err != nil {
		return nil, err
	}

	var AppConfig Config

	log.Info().Msg("Config loaded successfully")
	if err := viper.Unmarshal(&AppConfig); err != nil {
		return nil, err
	}

	return &AppConfig, nil
}
