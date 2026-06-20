import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../api/auth.service';
import { filter, map } from 'rxjs';

export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.currentUser$.pipe(
    filter(authState => authState.isInitialized),
    map(authState => {
      if (authState.isLoggedIn && authState.is_admin) {
        return true;
      }
      return router.createUrlTree(['/']);
    })
  );
};
