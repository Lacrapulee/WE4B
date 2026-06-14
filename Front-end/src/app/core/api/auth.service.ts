import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, tap } from 'rxjs';

export interface AuthResponse {
  success: boolean;
  message?: string;
  error?: string;
  user_id?: string | number;
}

export interface AuthState {
  isLoggedIn: boolean;
  user_id?: string | number | null;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly baseUrl = 'http://localhost:8000/api';
  
  private currentUserSubject = new BehaviorSubject<AuthState>({ isLoggedIn: false, user_id: null });
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    this.checkAuth().subscribe({ error: () => {} }); // Catch error silently on init
  }

  checkAuth(): Observable<any> {
    return this.http.get<{isLoggedIn: boolean, user_id?: string | number}>(`${this.baseUrl}/check_auth`, {
      withCredentials: true
    }).pipe(
      tap(response => {
        this.currentUserSubject.next({
          isLoggedIn: response.isLoggedIn,
          user_id: response.user_id || null
        });
      })
    );
  }

  login(credentials: { email: string; password: string }): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}/connexion`, credentials, {
      withCredentials: true // To support session cookies
    }).pipe(
      tap(response => {
        if (response.success && response.user_id) {
          this.currentUserSubject.next({ isLoggedIn: true, user_id: response.user_id });
        }
      })
    );
  }

  register(userData: any): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}/inscription`, userData, {
      withCredentials: true
    }).pipe(
      tap(response => {
        // Normally inscription might log you in, let's trigger checkAuth just in case
        this.checkAuth().subscribe({ error: () => {} });
      })
    );
  }
  
  logout(): Observable<any> {
    return this.http.get(`${this.baseUrl}/logout`, {
      withCredentials: true
    }).pipe(
      tap(() => {
        this.currentUserSubject.next({ isLoggedIn: false, user_id: null });
      })
    );
  }
}
