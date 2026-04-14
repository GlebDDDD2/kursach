<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\View;
use App\Models\User;
use App\Models\ViewingRequest;

final class ProfileController
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = (new User())->findById(Auth::id());
        if (!$user) {
            Auth::logout();
            Helpers::redirect('index.php?page=login');
        }

        $requests = (new ViewingRequest())->byUser(Auth::id());
        View::render('profile/index', [
            'title' => 'Профиль',
            'user' => $user,
            'requests' => $requests,
            'success' => Helpers::getFlash('success'),
            'error' => Helpers::getFlash('error'),
        ]);
    }

    public function requestDetails(): void
    {
        Auth::requireAuth();
        $requestId = max(0, (int) ($_GET['id'] ?? 0));
        $request = (new ViewingRequest())->detailForUser($requestId, Auth::id());
        if (!$request) {
            http_response_code(404);
            exit('Заявка не найдена или у вас нет прав на её просмотр.');
        }

        View::render('profile/request_details', [
            'title' => 'Детали заявки',
            'request' => $request,
        ]);
    }

    public function changePassword(): void
    {
        Auth::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('index.php?page=profile');
        }
        if (!Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            exit('Ошибка безопасности: неверный CSRF-токен.');
        }

        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            Helpers::flash('error', 'Заполните все поля формы смены пароля.');
            Helpers::redirect('index.php?page=profile');
        }
        if (strlen($newPassword) < 8) {
            Helpers::flash('error', 'Новый пароль должен содержать минимум 8 символов.');
            Helpers::redirect('index.php?page=profile');
        }
        if ($newPassword !== $confirmPassword) {
            Helpers::flash('error', 'Новые пароли не совпадают.');
            Helpers::redirect('index.php?page=profile');
        }

        $userModel = new User();
        $user = $userModel->findById(Auth::id());
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            Helpers::flash('error', 'Старый пароль введен неверно.');
            Helpers::redirect('index.php?page=profile');
        }

        $userModel->updatePassword(Auth::id(), $newPassword);
        Helpers::flash('success', 'Пароль успешно изменен.');
        Helpers::redirect('index.php?page=profile');
    }

    public function requestViewing(): void
    {
        Auth::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('index.php?page=catalog');
        }
        if (!Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            exit('CSRF Attack blocked');
        }

        $propertyId = max(0, (int) ($_POST['property_id'] ?? 0));
        $clientName = trim((string) ($_POST['client_name'] ?? ''));
        $clientPhone = trim((string) ($_POST['client_phone'] ?? ''));
        $clientEmail = trim((string) ($_POST['client_email'] ?? ''));
        $preferredDate = trim((string) ($_POST['preferred_date'] ?? ''));
        $comment = trim((string) ($_POST['comment'] ?? ''));

        if ($propertyId <= 0 || $clientName === '' || $clientPhone === '') {
            Helpers::flash('error', 'Заполните обязательные поля заявки.');
            Helpers::redirect('index.php?page=property&id=' . $propertyId);
        }

        $propertyModel = new \App\Models\Property();
        if (!$propertyModel->findPublishedById($propertyId)) {
            exit('Ошибка: попытка отправить заявку на несуществующий объект.');
        }

        $requestModel = new ViewingRequest();
        if ($requestModel->hasRecentDuplicate($propertyId, Auth::id(), 5)) {
            Helpers::flash('error', 'Повторную заявку на этот объект можно отправить не чаще одного раза в 5 минут.');
            Helpers::redirect('index.php?page=property&id=' . $propertyId);
        }

        $requestModel->create([
            'property_id' => $propertyId,
            'user_id' => Auth::id(),
            'client_name' => $clientName,
            'client_phone' => $clientPhone,
            'client_email' => $clientEmail !== '' ? $clientEmail : null,
            'preferred_date' => $preferredDate !== '' ? $preferredDate : null,
            'comment' => $comment !== '' ? $comment : null,
        ]);
        Helpers::flash('success', 'Заявка успешно отправлена.');
        Helpers::redirect('index.php?page=profile');
    }
}
