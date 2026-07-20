<?php
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
class AuthFilter implements FilterInterface
{
public function before(RequestInterface $request, $arguments = null)
{
$session = session();
// Si pas connecté → redirection login
if (!$session->get('id_user')) {
return redirect()->to('/login')->with('erreur', 'Connectez-vous
pour accéder à cette page');
}
}
public function after(RequestInterface $request, ResponseInterface
$response, $arguments = null)
{
// Rien à faire après
}
}