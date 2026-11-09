pipeline {
    agent any

    environment {
        APP_ENV = 'testing'
        COMPOSER_ALLOW_SUPERUSER = '1'
    }

    stages {
        stage('Checkout') {
            steps { checkout scm }
        }

        stage('Composer Install') {
            steps {
                sh 'cp -n .env.example .env || true'
                sh 'composer install --no-interaction --prefer-dist --no-progress'
                sh 'php artisan key:generate --force'
            }
        }

        stage('Composer Audit') {
            // Gagal jika ada kerentanan pada dependensi.
            steps { sh 'composer audit' }
        }

        stage('NPM Build') {
            when { expression { fileExists('package.json') } }
            steps {
                sh 'npm ci --ignore-scripts || npm install --ignore-scripts'
                sh 'npm run build'
            }
        }

        stage('PHPUnit + Coverage') {
            steps {
                sh '''
                    php -m | grep -i gd
                    vendor/bin/phpunit --coverage-clover coverage.xml --coverage-text --log-junit junit.xml | tee coverage.txt
                '''
            }
        }

        stage('Coverage Gate 100%') {
            // Gagal jika coverage Classes / Methods / Lines di app/ kurang dari 100%.
            steps {
                sh '''
                    for metric in Classes Methods Lines; do
                        value=$(grep -E "^\\s*${metric}:" coverage.txt | head -1 | grep -oE "[0-9]+\\.[0-9]+%" | head -1)
                        echo "$metric coverage: $value"
                        if [ "$value" != "100.00%" ]; then
                            echo "Coverage $metric kurang dari 100%" && exit 1
                        fi
                    done
                '''
            }
        }

        stage('Trivy Scan') {
            steps {
                sh 'trivy fs --exit-code 0 --severity HIGH,CRITICAL --skip-dirs vendor,node_modules .'
            }
        }

        stage('Package') {
            steps {
                sh "zip -r latest-app.zip . -x 'vendor/*' 'node_modules/*' '.git/*' '.env' 'coverage*' 'junit.xml' 'latest-app.zip'"
                archiveArtifacts artifacts: 'latest-app.zip', fingerprint: true
            }
        }
    }

    post {
        always { junit allowEmptyResults: true, testResults: 'junit.xml' }
    }
}
