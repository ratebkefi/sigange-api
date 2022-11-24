<?php


namespace App\Controller;

use App\Entity\User;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Security\TokenGenerator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserForgetPasswordController extends BaseController
{
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;
    protected MailerInterface $mailer;
    protected ParameterBagInterface $parameterBag;
    protected TokenGenerator $tokenGenerator;
    protected UserPasswordEncoderInterface $passwordEncoder;


    public function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        MailerInterface $mailer,
        ParameterBagInterface $parameterBag,
        TokenGenerator $tokenGenerator,
        UserPasswordEncoderInterface $passwordEncoder

    ) {
        parent::__construct($em, $serializer);
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route(
     *     name="forgot_password",
     *     path="/forgot_password",
     *     methods={"POST"},
     *     defaults={
     *      "_controller": "App\Controller\UserForgetPasswordController:forgotPasswordAction",
     *     }
     * )
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request): JsonResponse
    {
        // we are looking for the user associated with the email
        try {
            $parameters = json_decode($request->getContent(), true);
            $email = $parameters['email'];
            $em = $this->em;
            $user = $em->getRepository('App:User')->findOneBy(["email" => $email]);

            if(!$user) return $this->createJsonErrorResponse("Bad request", '', Response::HTTP_BAD_REQUEST);

            // Generate a new token
            $token = $this->tokenGenerator->getRandomSecureToken();
            $user->setActivatedToken($token);
            $user->setActivatedTokenAt(new DateTime());
            $em->persist($user);
            $em->flush();
            // send an email containing the link for reset password with the user
            $url_reset_password = $this->parameterBag->get('app.url_reset_password');
            $this->sendMail($email, $user, $url_reset_password, $token);
            return new JsonResponse((json_decode('SUCCESS', true)));
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function sendMail(string $to,
                             User $user,
                             string $url_reset_password,
                             string $token)
    {
        // Send an email containing the link for reset password with the user
        $fromAddress = $this->parameterBag->get('app.email_from_noreply');
        $emailFromName = $this->parameterBag->get('app.email_from_name');
        $emailSignatureName = $this->parameterBag->get('app.email_signature_name');

        $message = (new TemplatedEmail())
            ->context([
                'user' => $user,
                'url_reset_password' => $url_reset_password,
                'token' => $token,
                'email_signature_name' => $emailSignatureName
            ])
            ->from(new Address($fromAddress, $emailFromName))
            ->to($to)
            ->subject("Password Reset")
            ->htmlTemplate('email/forgotPassword.html.twig');
        $this->mailer->send($message);

    }


    /**
     * @Route(
     *     name="reset_password",
     *     path="/reset_password",
     *     methods={"POST"},
     *     defaults={
     *      "_controller": "App\Controller\UserForgetPasswordController:resetPasswordAction",
     *     }
     * )
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request): JsonResponse
    {

        try {
            $parameters = json_decode($request->getContent(), true);
            $password = $parameters['password'];
            $token = $parameters['token'];
            $em = $this->em;
            // we are looking for the user associated with the token
            $user = $em->getRepository('App:User')->findOneBy(["activatedToken" => $token]);

            if(!$user) return $this->createJsonErrorResponse("Bad request", '', Response::HTTP_BAD_REQUEST);

            $diff = date_diff(new DateTime(), $user->getActivatedTokenAt());
            // if the token creation date is greater than 24 eurs, an error is returned
            if(($diff->format("%h"))>24)
            return $this->createJsonErrorResponse("Expired link", $token, Response::HTTP_BAD_REQUEST);
            $user->setPassword( $this->passwordEncoder->encodePassword( $user, $password));
            // we generate a new token so that the current link expires
            $token = $this->tokenGenerator->getRandomSecureToken();
            $user->setActivatedToken($token);
            $user->setActivatedTokenAt(new DateTime());
            $em->persist($user);
            $em->flush();

            return new JsonResponse((json_decode('SUCCESS', true)));
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


}
