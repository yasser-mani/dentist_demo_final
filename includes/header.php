<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2563eb">
    <title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-container">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <aside class="sidebar" id="sidebar" aria-label="Navigation principale">
        <div class="sidebar-header">
            <img src="assets/img/logo.svg" alt="DentaFlow" class="sidebar-logo">
            <div>
                <div class="sidebar-title">DentaFlow</div>
                <div class="sidebar-subtitle">Gestion du cabinet</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="index.php">
                <span class="nav-icon">⌂</span><span>Tableau de bord</span>
            </a>
            <a class="nav-link <?= in_array($currentPage, ['patients', 'patient'], true) ? 'active' : '' ?>" href="patients.php">
                <span class="nav-icon">●</span><span>Patients</span>
            </a>
            <a class="nav-link <?= $currentPage === 'appointments' ? 'active' : '' ?>" href="appointments.php">
                <span class="nav-icon">◫</span><span>Rendez-vous</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="doctor-card">
                <div class="avatar">DD</div>
                <div>
                    <strong>Dr. Dupont</strong>
                    <span>Chirurgien-dentiste</span>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Ouvrir le menu" aria-expanded="false">☰</button>
                <div>
                    <div class="eyebrow">DentaFlow</div>
                    <h1 class="page-title" id="pageTitle"><?= e($pageTitle) ?></h1>
                </div>
            </div>
            <div class="top-bar-actions">
                <span class="user-greeting">Bienvenue, Dr. Dupont</span>
            </div>
        </header>
        <div class="content-wrapper">
