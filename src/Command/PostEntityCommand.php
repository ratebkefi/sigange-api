<?php


namespace App\Command;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Http post request to send an entity to a webhook
 * Class PostEntityToWebhook
 * @package App\Command
 */
class PostEntityCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $client;

    private LoggerInterface $logger;


    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        HttpClientInterface $client,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('post:entity')
            ->setDescription('Post an entity to an endpoint.')
            ->setDefinition(array(
                new InputArgument('code', InputArgument::REQUIRED, 'The code to identify the entity'),
                new InputArgument('class', InputArgument::REQUIRED, 'The class name of entity'),
                new InputArgument('url', InputArgument::REQUIRED, 'The url where to post data'),
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $class = $input->getArgument('class');
        $url = $input->getArgument('url');

        $this->logger->debug('PostEntityCommand : with inputs :', [$code, $class, $url]);

        $entity = $this->entityManager->getRepository($class)->findOneBy(['code' => $code]);

        /**
         * Empty groups context will serialize to that:
         * {
         *   "@context": "/api/contexts/Site",
         *   "@id": "/api/sites/6845cdfa-8932-4f62-84bb-568edbcfff53",
         *   "@type": "Site"
         *  }
         */
        $serializedEntity = $this->serializer->serialize($entity, 'jsonld', ['groups' => []]);

        $responseStatusCode = $this->postEntityInformation($serializedEntity, $url);
        // TODO do something if http request fail ?
        if ($responseStatusCode === Response::HTTP_OK) {
            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }

    }

    public function postEntityInformation(string $entity, string $url): int
    {
        $response = $this->client->request(
            'POST',
            $url, [
            'body' => $entity,
        ]);

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];

        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        // $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $statusCode;
    }

}
