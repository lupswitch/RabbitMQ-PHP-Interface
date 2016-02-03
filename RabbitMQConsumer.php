<?php

/**
 * Jérémy Ferrero<br/>
 * Compilatio <br/>
 * GETALP - Laboratory of Informatics of Grenoble <br/>
 *
 * This work is licensed under a Creative Commons Attribution-ShareAlike 4.0 International License.
 * For more information, see http://creativecommons.org/licenses/by-sa/4.0/
 */
/* * *****************************************************************************************
 *                                      CONSTANTES
 * **************************************************************************************** */

/**
 * Nombre maximal de processus (worker lancé) permis par CPU.
 */
define('__MAX_PROCESS_BY_CPU__', 8);

/**
 * Nombre maxiaml d'opérations que peut et doit effectuer un worker avant de se détruire et de rappeler un autre worker.
 */
define('__MAX_OPERATION_BY_CONSUMER__', 10000);

/**
 * Nombre maximal de minutes que peut survivre un worker avant de se détruire et de rappeler un autre worker.
 */
define('__MAX_MINUTE_BY_CONSUMER__', 30);

/**
 * @class RabbitMQConsumer
 * 
 * Classe permettant d'écouter une queue (file d'attente) et de récupérer des messages par le biais du service RabbitMQ.
 */
class RabbitMQConsumer {
    /*     * *****************************************************************************************
     *                                      VARIABLES
     * **************************************************************************************** */

    /**
     * Adresse IP du serveur où le service RabbitMQ est installé.
     * @var string  Adresse IP host. 
     */
    var $host = '';

    /**
     * Port de connexion.
     * @var int Port. 
     */
    var $port = '';

    /**
     * Utilisateur pour authentification auprès du service RabbitMQ.
     * @var string  Nom de l'utilisateur. 
     */
    var $user = '';

    /**
     * Mot de passe de l'utilisateur pour l'authetification au service RabbitMQ.
     * @var string  Mot de passe. 
     */
    var $password = '';

    /**
     * Nom de la queue (la file d'attente).
     * @var string  Nom de la queue. 
     */
    var $queueName = 'test';

    /**
     * Nombre d'opérations maximales qu'un consumer peut effectuer avant de se relancer. 
     * @var int $operationsNumber
     */
    var $operationsNumber = 0;

    /**
     * Connexion de l'instance RabbitMQ.
     * @var AMQPConnection  Instance de la connexion. 
     */
    var $connection = null;

    /**
     * Cannal d'envoi de l'instance RabbitMQ.
     * @var  AMQPChannel    Cannal.
     */
    var $channel = null;

    /**
     * Queue de l'instance RabbitMQ.
     * @var AMQPQueue   Queue. 
     */
    var $queue = null;
    
    /**
     * Chemin d'accès jusqu'au worker.
     * @var string  $workerPath 
     */
    private $workerPath = '';

    /*     * *****************************************************************************************
     *                                      CONSTRUCTEUR
     * **************************************************************************************** */

    /**
     * Constructeur par défaut de la classe RabbitMQConsumer.
     */
    public function __construct() {
        
    }

    /*     * *****************************************************************************************
     *                                      SETTERS
     * **************************************************************************************** */

    /**
     * Affecte un hôte de connexion à l'instance RabbitMQProducer.
     * @param   string  $host   Adresse IP.
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Affecte un port de connexion à l'instance RabbitMQProducer.
     * @param   int $port   Port de connexion.
     */
    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * Affecte un utilisateur pour authentification à la connexion à l'instance RabbitMQProducer.
     * @param   string  $user   Nom de l'utilisateur.
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * Affecte un mot de passe pour l'authetification à la connexion à l'instance RabbitMQProducer.
     * @param   string  $password   Mot de passe de l'utilisateur.
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Affecte un nom de queue à l'instance RabbitMQProducer.
     * @param   string  $name   Nom de la queue.
     */
    public function setQueueName($name) {
        $this->queueName = $name;
    }
    
    /**
     * Affecte un chemin d'accès au worker.
     * @param   string  $workerPath
     */
    public function setWorkerPath($workerPath) {
        $this->workerPath = $workerPath;
    }

    /*     * *****************************************************************************************
     *                                      GETTERS
     * **************************************************************************************** */

    /**
     * Retourne l'hôte de la connexion courante.
     * @return  string  Adresse IP.
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Retourne le port de la connexion courante.
     * @return  int Port.
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Retourne l'utilisateur de la connexion courante.
     * @return  string  Nom d'utilisateur.
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Retourne le nom de la queue de l'instance courante.
     * @return  string  Nom de la queue.
     */
    public function getQueueName() {
        return $this->queueName;
    }

    /*     * *****************************************************************************************
     *                                      METHODES
     * **************************************************************************************** */

    /**
     * Établie la connexion au service RabbitMQ.
     * @param   string  $host       Adresse IP.
     * @param   int     $port       Port de connexion.
     * @param   string  $user       Nom d'utilisateur.
     * @param   string  $password   Mot de passe de l'utilisateur.
     */
    public function connect($host = -1, $port = -1, $user = -1, $password = -1) {
        if ($host == -1) {
            $host = $this->host;
        }
        if ($port == -1) {
            $port = $this->port;
        }
        if ($user == -1) {
            $user = $this->user;
        }
        if ($password == -1) {
            $password = $this->password;
        }
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;

        try {
            // On vérifie que le serveur ne soit pas trop chargé.
            $cpuNumber = (int) shell_exec('cat /proc/cpuinfo | grep processor | wc -l');
            
            $load = (float) preg_replace('~ .*~', '', file_get_contents('/proc/loadavg'));
            if ($load > $cpuNumber) {
                echo 'La charge du serveur (' . $load . ') est superieure au nombre de CPUs (' . $cpuNumber . ')' . '<br/ >';
                // Gérer l'exception ici !
            }
            
            // On vérifie qu'il n'y est pas déjà trop de consumers en cours.
            $consumerNumber = (int) shell_exec('ps x | grep ' . $this->workerPath . ' | grep -v grep | wc -l');
            if ($consumerNumber > __MAX_PROCESS_BY_CPU__ * $cpuNumber) {
                echo 'Il y a trop de consumers(' . $consumerNumber . ') compte tenu du nombre de CPUs(' . $cpuNumber . ')' . '<br/ >';
                // Gérer l'exception ici !
            }
            
            // Connexion.
            $this->connection = new AMQPConnection();
            $this->connection->setHost($this->host);
            $this->connection->setPort($this->port);
            $this->connection->setLogin($this->user);
            $this->connection->setPassword($this->password);
            if (!$this->connection->connect()) {
                echo 'Impossible de se connecter au service RabbitMQ.' . '<br/ >';
                // Gérer l'exception ici !
            }
            // Créer un cannal de discussion.
            $this->channel = new AMQPChannel($this->connection);
        } catch (ProcessException $e) {
            die($e);
        }
    }

    /**
     * Déconnecte l'instance du service RabbitMQ.
     */
    public function disconnect() {
        try {
            if (!$this->connection->disconnect()) {
                echo 'Impossible de se déconnecter du service RabbitMQ.' . '<br/ >';
                // Gérer l'exception ici !
            }
        } catch (ProcessException $e) {
            die($e);
        }
    }

    /**
     * Déclare une queue.
     * @param   string  $name   Nom de queue.
     */
    public function declareQueue($name = -1) {
        if ($name == -1) {
            $name = $this->queueName;
        }
        $this->queueName = $name;
        $this->queue = new AMQPQueue($this->channel);
        $this->queue->setName($this->queueName);
        $this->queue->setFlags(AMQP_DURABLE);
        $this->queue->declareQueue();
    }

    /**
     * Écoute le cannal et dépile des messages de la queue. <br/>
     * Chaque message est envoyé à la fonction callback.
     */
    public function listen() {
        // Écoute le cannal et consumme des messages en les envoyant dans la fonction callback.
        // Ne sort jamais de la méthode consume sauf si callback retourne false.
        $this->queue->consume(array($this, "callback"));
        // Une fois sortie de la boucle d'écoute.
        // Déconnexion.
        $this->disconnect();
        // Le consumer se relance lui même en tâche de fond pour ne pas attendre le cron.
        shell_exec('php ' . $this->workerPath . ' >> /dev/null 2>&1 &');
    }

    /**
     * Écoute le cannal et dépile des messages de la queue. <br/>
     * Chaque message est envoyé à la méthode de la classe passée en paramètre.
     * @param   array   $params     array(classe, méthode) à appeler.
     */
    public function externalListen($params) {
        // Écoute le cannal et consumme des messages en les envoyant dans la fonction callback.
        // Ne sort jamais de la méthode consume sauf si callback retourne false.
        $this->queue->consume($params);
        // Une fois sortie de la boucle d'écoute.
        // Déconnexion.
        $this->disconnect();
        // Le consumer se relance lui même en tâche de fond pour ne pas attendre le cron.
        shell_exec('php ' . $this->workerPath . ' >> /dev/null 2>&1 &');
    }

    /**
     * Extrait une chaîne de caractère d'une envelope RabbitMQ.
     * @param   AMPQEnvelope    $envelope   Envelope RabbitMQ.
     * @return  string          Message.
     */
    public function extractMessage($envelope) {
        return $envelope->getBody();
    }

    /**
     * Signale à la queue que le message à correctement était traité et qu'il peut être dépilé.
     * @param   AMPQEnvelope    $envelope   Envelope RabbitMQ.
     */
    public function acknowledge($envelope) {
        $this->queue->ack($envelope->getDeliveryTag());
    }

    /**
     * Retourne si le consumer peut encore exécuter des tâches ou non.
     * @param   int         $time   Temps.
     * @return  boolean     <b>TRUE</b> si le consumer peut encore effectuer des tâches, <b>FALSE</b> sinon.
     */
    public function verifyLimit($time = null) {
        $test = true;
        // Si un temps d'execution est passé en paramètre.
        if ($time != null) {
            // On retourne false si le worker s'est exécuté depuis plus de X minutes.
            if ((microtime(true) - strtotime($time)) >= (__MAX_MINUTE_BY_CONSUMER__ * 60)) {
                $test = false;
            }
        } else {
            // On retourne false si le worker a effectué plus de X opérations.
            if (++$this->operationsNumber >= __MAX_OPERATION_BY_CONSUMER__) {
                $test = false;
            }
        }
        return $test;
    }

    /**
     * Méthode éxecutée lors de la consommation de chaque message.
     * @param    AMQPEnvelope   $envelope   Une enveloppe contenant le message dépilé.
     */
    public function callback($envelope) {
        // On récupère le message.
        $message = $this->extractMessage($envelope);
        // Faire quelque chose du message.
        // Do something with the message content.
        // On procéde à un acknoledge sur la queue pour lui signaler que l'on a fini de traiter ce message ci et qu'elle peut le dépiler.
        $this->acknowledge($envelope);
        // On vérifie si le worker doit être détruit ou non.
        $this->verifyLimit();
    }

}
