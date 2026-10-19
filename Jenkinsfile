pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'devspark-app:ci'
    }

    stages {
        stage('1. Checkout Code') {
            steps {
                echo '📥 Récupération du code source depuis GitHub...'
                checkout scm
            }
        }

        stage('2. Build Docker Image') {
            steps {
                echo '🐳 Construction de l\'image Docker pour les tests et la CI...'
                script {
                    if (isUnix()) {
                        sh "docker build -t ${DOCKER_IMAGE} ."
                    } else {
                        bat "docker build -t %DOCKER_IMAGE% ."
                    }
                }
            }
        }

        stage('3. Install Dependencies & Build Assets') {
            steps {
                echo '📦 Installation de Composer et compilation Vite dans le conteneur...'
                script {
                    if (isUnix()) {
                        sh """
                            docker run --rm -v "\${WORKSPACE}:/var/www/html" -w /var/www/html ${DOCKER_IMAGE} bash -c "
                                composer install --no-interaction --prefer-dist --optimize-autoloader &&
                                cp -n .env.example .env || true &&
                                php artisan key:generate --env=testing &&
                                npm install &&
                                npm run build
                            "
                        """
                    } else {
                        bat """
                            docker run --rm -v "%cd%:/var/www/html" -w /var/www/html %DOCKER_IMAGE% bash -c "composer install --no-interaction --prefer-dist --optimize-autoloader && cp -n .env.example .env || true && php artisan key:generate --env=testing && npm install && npm run build"
                        """
                    }
                }
            }
        }

        stage('4. Run Tests in Docker') {
            steps {
                echo '🧪 Exécution des tests PHPUnit dans le conteneur Docker...'
                script {
                    if (isUnix()) {
                        sh """
                            docker run --rm -v "\${WORKSPACE}:/var/www/html" -w /var/www/html \
                                -e APP_ENV=testing \
                                -e DB_CONNECTION=sqlite \
                                -e DB_DATABASE=:memory: \
                                ${DOCKER_IMAGE} php artisan test --log-junit reports/phpunit.xml
                        """
                    } else {
                        bat """
                            docker run --rm -v "%cd%:/var/www/html" -w /var/www/html -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: %DOCKER_IMAGE% php artisan test --log-junit reports/phpunit.xml
                        """
                    }
                }
            }
        }
    }

    post {
        always {
            echo '📊 Publication des résultats de tests...'
            junit testResults: 'reports/phpunit.xml', allowEmptyResults: true
        }
        cleanup {
            echo '🧹 Nettoyage de l\'image Docker temporaire...'
            script {
                try {
                    if (isUnix()) {
                        sh "docker rmi -f ${DOCKER_IMAGE} || true"
                    } else {
                        bat "docker rmi -f %DOCKER_IMAGE% || exit 0"
                    }
                } catch (Exception e) {
                    echo "Nettoyage ignoré : ${e.getMessage()}"
                }
            }
        }
    }
}
