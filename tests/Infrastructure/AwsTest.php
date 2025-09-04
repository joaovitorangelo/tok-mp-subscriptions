<?php

use PHPUnit\Framework\TestCase;
use Aws\Sqs\SqsClient;
use Aws\SecretsManager\SecretsManagerClient;

class AwsTest extends TestCase
{
    private $queueUrl = 'https://sqs.us-east-2.amazonaws.com/848647692411/tok-mp-subscriptions-jobs';
    private $region   = 'us-east-2';
    private $secretId = 'tok-mp-plugin/credentials';

    public function testAwsIntegration()
    {
        $smClient = new SecretsManagerClient([
            'region'  => $this->region,
            'version' => 'latest',
        ]);

        $result = $smClient->getSecretValue(['SecretId' => $this->secretId]);
        $secret = json_decode($result['SecretString'], true);

        $this->assertArrayHasKey('AWS_KEY', $secret);
        $this->assertArrayHasKey('AWS_SECRET', $secret);

        $sqsClient = new SqsClient([
            'region'  => $this->region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $secret['AWS_KEY'],
                'secret' => $secret['AWS_SECRET'],
            ],
        ]);

        $send = $sqsClient->sendMessage([
            'QueueUrl'    => $this->queueUrl,
            'MessageBody' => json_encode(['test' => 'ok', 'time' => time()]),
        ]);

        $this->assertArrayHasKey('MessageId', $send);

        // Salva resultado no option do WordPress
        update_option('tok_aws_test_result', $send['MessageId']);
    }
}
