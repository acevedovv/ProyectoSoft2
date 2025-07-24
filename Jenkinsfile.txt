pipeline {
    agent any

    stages {
        stage('pruebas ') {
            steps {
                sh '''
                apt-get update && apt-get install docker.io -y
                '''
                sh '''
                docker exec gateway sh -c "php artisan test"
                '''
                
            }
        }
}
}