<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(StatsService $statsService, EntityManagerInterface $manager)
    {
        $stats = $statsService->getStats();

        $bestAds = $statsService->getAdsStats('DESC');
        $worstAds = $statsService->getAdsStats("ASC");
        $lastAds = $statsService->getLastAds("DESC");
        $lastBookings = $statsService->getLastBookings("DESC");


        return $this->render('admin/dashboard/index.html.twig', [
            // Compact permet d'associée la clé avec la variable de même nom 
            "stats" => $stats,
            "bestAds" => $bestAds,
            "worstAds" => $worstAds,
            "lastAds" => $lastAds,
            "lastBookings" => $lastBookings
        ]);
    }
}
