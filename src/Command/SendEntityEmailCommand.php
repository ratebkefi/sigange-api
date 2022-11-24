<?php

namespace App\Command;

use App\Entity\UserGroup;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Send an email corresponding to the creation of an entity to the corresponding recipient.
 *
 * Example:
 * $ bin/console entity-email:send '9ab752ff-fdaa-45ca-9234-9bc035241b43' 'Site' '9fa05c10-c6ea-4b48-8f4d-8f5c418da0e5' 'amoore@hexaglobe.com'
 *
 * Class SendEntityEmailCommand
 * @package App\Command
 */
class SendEntityEmailCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    private ParameterBagInterface $parameterBag;

    private $formatter;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this
            ->setName('entity-email:send')
            ->setDescription('Send an email to notify when a device has been created')
            ->addArgument('code', InputArgument::REQUIRED, 'Code of the entity')
            ->addArgument('class', InputArgument::REQUIRED, 'Class name of the entity')
            ->addArgument('usergroup-code', InputArgument::REQUIRED, 'Code of the usergroup')
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient of the email')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->formatter = $this->getHelper('formatter');

        $code = $input->getArgument('code');
        $class = 'App:' . $input->getArgument('class');
        $userGroupCode = $input->getArgument('usergroup-code');
        $to = $input->getArgument('to');
        $output->writeln([
            "Starting email creation addressed to $to",
            '=================================',
        ]);

        // Get the necessary data for the email
        try {
            $userGroup = $this->entityManager->getRepository(UserGroup::class)->findOneBy(['code' => $userGroupCode]);
            $entity = $this->entityManager->getRepository($class)->findOneBy(['code' => $code]);
        } catch (Exception $exception) {
            return $this->outputErrorMessage($exception, $this->formatter, $output);
        }


        if (!$userGroup && !$userGroup instanceof UserGroup) {
            return $this->outputErrorMessage(new Exception(), $this->formatter, $output);
        }
        $output->writeln([
            'Before sending mail ',
            '=================================',
        ]);
        try {
            $this->sendMail($entity, $userGroup, $class, $to, $output);
            $io->success('Email sent');
            return Command::SUCCESS;

        } catch (Exception $exception) {
            return $this->outputErrorMessage($exception, $this->formatter, $output);
        }


    }

    public function sendMail(
        $entity,
        UserGroup $userGroup,
        string $className,
        string $to,
        OutputInterface $output
    ) {

        $beautifiedClassName = str_replace('App:', '', $className);

        $fromAddress = $this->parameterBag->get('app.email_from_noreply');
        $emailFromName = $this->parameterBag->get('app.email_from_name');

        $message = (new TemplatedEmail())
            ->context([
                'userGroup' => $userGroup,
                'entity' => $entity,
                'className' => $beautifiedClassName
            ])
            ->from(new Address($fromAddress, $emailFromName))
            ->to($to)
            ->subject("A new $beautifiedClassName has been added")
            ->htmlTemplate('email/entityCreatedEmail.html.twig');

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            $this->outputErrorMessage($exception, $this->formatter, $output);
        }
    }

    /**
     * @param Exception $exception
     * @param FormatterHelper $formatter
     * @param OutputInterface $output
     * @return int
     */
    protected function outputErrorMessage(
        Exception $exception,
        FormatterHelper $formatter,
        OutputInterface $output
    ): int {
        $errorMessages = ['Error sending email', $exception->getMessage()];
        $formattedBlock = $formatter->formatBlock($errorMessages, 'error');
        $output->writeln($formattedBlock);
        return Command::FAILURE;
    }

}
