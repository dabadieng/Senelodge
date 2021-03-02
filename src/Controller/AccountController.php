<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mailer;
use App\Service\Mailler;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{

    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailler $mailler)
    {
        $this->mailler = $mailler;
    }
    /**
     *Permet de se connecter
     *  @Route("/login", name="account_login")
     * @return Response
     */
    public function login(AuthenticationUtils $utile)
    {
        //renvoie un booléan en cas d'erreur
        $error = $utile->getLastAuthenticationError();

        //permet de ne pas retapper l'adresse mail en cas d'erreur
        $username = $utile->getLastUsername();

        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
        ]);
    }
    /**
     * permet de se déconnecter
     * @Route("/logout", name="account_logout")
     *
     * @return void
     */
    public function logout()
    {
        //rien
    }

    /**
     * Permet d'afficher le formulaire d'inscription
     * @Route("/register" , name="account_register")
     *
     * @return Response
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get("picture")->getData();

            if ($image !== null) {
                //On génère un nouveau nom de fichier 
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                //On copie le fichier dans le dossier upload 
                $image->move(
                    //Lieu de stockage défini dans service.yml 
                    $this->getParameter("image_directory"),
                    //nom du fichier à déplacer dans le dossier 
                    $fichier
                );

                $user->setPicture($fichier);
            }

            $hash = $encoder->encodePassword($user, $user->getHash());
            $user->setHash($hash);

            //Demande de validation par mail 
            $user->setToken($this->generateToken());

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre compte à bien été créé ! Veuillez le valider via le lien reçu par mail pour finaliser votre inscription et profiter de nos services!'
            );

            $this->mailler->sendEmail($user->getEmail(), $user->getToken());

        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirmer-mon-compte/{token}", name="confirm_account")
     * @param string $token
     */
    public function confirmAccount(string $token, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(["token" => $token]);
        if ($user) {
            $user->setToken(null);
            $user->setEnabled(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "Compte actif !");
            return $this->redirectToRoute("homepage");
        } else {
            $this->addFlash("error", "Ce compte n'exsite pas !");
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * Permet d'afficher et de traiter le formulaire de profil
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function profile(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Les modificatons ont bien été enregistées !'
            );

            return $this->redirectToRoute('homepage');
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * Permet de modifier le mot de passe
     *@Route("/account/password-update", name="account_password")
     *@IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $passwordUpdate = new PasswordUpdate();

        //renvoi l'utilisateur connecté
        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                //Gérer l'erreur avec l'API symfonyformapi
                $form->get("oldPassword")->addError(new FormError("Le mot de passe saisie n'est pas votre mot de passe actuel"));
            } else {
                $hash = $encoder->encodePassword($user, $passwordUpdate->getNewPassword());
                $user->setHash($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    "success",
                    "Votre mot de passe à bien été modifié"
                );
                return $this->redirectToRoute("homepage");
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher le profil de l'utilisateur connecté 
     *@Route("/account", name="account_index")
     *@IsGranted("ROLE_USER")
     * 
     * @return Response 
     */
    public function myAccount()
    {
        return $this->render("user/index.html.twig", [
            "user" => $this->getUser()
        ]);
    }

    /**
     * Permet d'afficher les réservations faites par l'utilisateur
     * @Route("/account/bookings", name="account_booking")
     *
     * @return Response 
     */
    public  function bookings()
    {
        return $this->render("account/bookings.html.twig");
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
