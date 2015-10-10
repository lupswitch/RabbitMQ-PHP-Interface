<?php

/**
 * Jérémy Ferrero<br/>
 * Compilatio <br/>
 * GETALP - Laboratory of Informatics of Grenoble <br/>
 *
 * This work is licensed under a Creative Commons Attribution-ShareAlike 4.0 International License.
 * For more information, see http://creativecommons.org/licenses/by-sa/4.0/
 */

/**
 * @class RabbitMQProducer
 * 
 * Classe permettant d'envoyer des messages dans une queue (file d'attente) par le biais du service RabbitMQ.
 */
class RabbitMQProducer {
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
     * Exchange de l'instance RabbitMQ.
     * @var  AMQPExchange   Exchange. 
     */
    var $exchange = null;

    /**
     * Message à envoyer dans la queue par le biais du cannal.
     * @var string  Message. 
     */
    var $message = '';

    /*     * *****************************************************************************************
     *                                      CONSTRUCTEUR
     * **************************************************************************************** */

    /**
     * Constructeur par défaut de la classe RabbitMQProducer.
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
     * Affecte un message à l'instance RabbitMQProducer.
     * @param   string  $message    Message.
     */
    public function setMessage($message) {
        $this->message = $message;
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
            $this->exchange = new AMQPExchange($this->channel);
        } catch (ProcessException $e) {
            die($e);
        }
    }

    /**
     * Déconnecte l'instance du service RabbitMQ.
     */
    public function disconnect() {
        // Ferme le cannal puis ferme la connexion.
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
     * Ouvre une transaction.
     */
    public function startTransaction() {
        $this->channel->startTransaction();
    }
    
    /**
     * Commit (Ajoute) le(s) message(s) en cours de publication (dans le pipe) à la queue.
     */
    public function commitTransaction() {
        $this->channel->commitTransaction();
    }

    /**
     * Publie un message dans le pipe.
     * @param   mixed   $message    Message à envoyer.<br/>
     * Par défaut un string est recommandé. Si un tableau est envoyé, il sera sérialisé en string.
     */
    public function send($message = -1) {
        if ($message == -1) {
            $message = $this->message;
        }
        // Si le message est un tableau, il est sérialisé.
        if (is_array($message)) {
            $message = $this->serializeMessage($message);
        }
        // Publie le message dans la queue.
        $this->exchange->publish($message, $this->queueName, AMQP_NOPARAM, array('delivery_mode' => 2));
    }

    /**
     * Retourne le nombre de messages encore présents dans la queue.
     * @return  int Nombre de messages.
     */
    public function getMessageNumberInQueue() {
        $vhost = '/';
        $queue = $this->queueName;

        $host = $this->host;
        $port = '???'; // Caution ! The API port may be different to the standard port.
        $user = $this->user;
        $pass = $this->password;

        $period = 20;

        $rates = round($period / 10);
        if ($rates < 1) {
            $rates = 1;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$host:$port/api/queues/" . urlencode($vhost) . "/$queue?msg_rates_age=$period&msg_rates_incr=$rates");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
        $res = curl_exec($ch);
        curl_close($ch);

        $split = preg_split('~\n~', $res);
        $httpStatus = trim($split[0]);
        $contentType = preg_replace('~.+: ~', '', trim($split[3]));
        $contentLength = (int) preg_replace('~.+: ~', '', trim($split[4]));
        $data = $split[7];

        if (!(preg_match('~200 OK~', $httpStatus) and $contentLength > 0 and $contentType == 'application/json')) {
            die("Erreur lors de la recuperation des infos.");
        }
        $json = json_decode($data);
        $messagesNumber = $json->messages;

        return $messagesNumber;
    }

    /*     * *****************************************************************************************
     *                                      OPERATEURS
     * **************************************************************************************** */

    /**
     * Retourne une chaîne sérialisé.
     * @param   mixed   $message    Message à sérialiser.
     * @return  string  Message sérialisé.
     */
    public function serializeMessage($message) {
        return serialize($message);
    }

}
