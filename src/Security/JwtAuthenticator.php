<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\Authentication\JwtAccessTokenService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class JwtAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly JwtAccessTokenService $jwtAccessTokenService,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with((string) $request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authorization = (string) $request->headers->get('Authorization');
        $token = trim(substr($authorization, 7));

        if ('' === $token) {
            throw new CustomUserMessageAuthenticationException('Missing authentication token.');
        }

        try {
            $payload = $this->jwtAccessTokenService->decode($token);
        } catch (ExpiredException) {
            throw new CustomUserMessageAuthenticationException('Access token has expired.');
        } catch (SignatureInvalidException|\UnexpectedValueException|\InvalidArgumentException) {
            throw new CustomUserMessageAuthenticationException('Invalid access token.');
        }

        return new SelfValidatingPassport(
            new UserBadge($payload['email'], function (string $userIdentifier) {
                $user = $this->userRepository->findOneByEmail($userIdentifier);

                if (null === $user) {
                    throw new CustomUserMessageAuthenticationException('Invalid access token.');
                }

                return $user;
            }),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => [
                'code' => 'unauthorized',
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new JsonResponse([
            'error' => [
                'code' => 'unauthorized',
                'message' => 'Authentication required.',
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }
}
