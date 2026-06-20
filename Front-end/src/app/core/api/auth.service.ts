import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, tap } from 'rxjs';

export interface AuthResponse {
  message?: string;
  error?: string;
  result?: string | number | null;
}

export interface AuthState {
  isInitialized: boolean;
  isLoggedIn: boolean;
  user_id?: string | number | null;
  is_admin?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly baseUrl = 'http://localhost:8000/api/';
  
  private currentUserSubject = new BehaviorSubject<AuthState>({ isInitialized: false, isLoggedIn: false, user_id: null, is_admin: false });
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    // Vérifie la session au démarrage en appelant une route protégée légère
    this.checkAuth();
  }

  checkAuth(): void {
    this.http.get<any>(`${this.baseUrl}?action=check_auth`, { withCredentials: true })
      .subscribe({
        next: (response) => {
          if (response?.isLoggedIn) {
            this.currentUserSubject.next({
              isInitialized: true,
              isLoggedIn: true,
              user_id: response.user_id,
              is_admin: !!response.is_admin
            });
          } else {
            this.currentUserSubject.next({ isInitialized: true, isLoggedIn: false, user_id: null, is_admin: false });
          }
        },
        error: () => {
          this.currentUserSubject.next({ isInitialized: true, isLoggedIn: false, user_id: null, is_admin: false });
        }
      });
  }

  login(credentials: { email: string; password: string }): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}?action=connexion`, credentials, {
      withCredentials: true
    }).pipe(
      tap(response => {
        if (response.result) {
          this.currentUserSubject.next({
            isInitialized: true,
            isLoggedIn: true,
            user_id: response.result,
            is_admin: !!(response as any).is_admin
          });
        }
      })
    );
  }

  register(userData: any): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}?action=inscription`, userData, {
      withCredentials: true
    }).pipe(
      tap(response => {
        if (response.result) {
          this.currentUserSubject.next({
            isInitialized: true,
            isLoggedIn: true,
            user_id: response.result,
            is_admin: false
          });
        }
      })
    );
  }
  
  logout(): Observable<any> {
    return this.http.get(`${this.baseUrl}?action=logout`, {
      withCredentials: true
    }).pipe(
      tap(() => {
        this.currentUserSubject.next({ isInitialized: true, isLoggedIn: false, user_id: null, is_admin: false });
      })
    );
  }
}