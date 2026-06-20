import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../models/catalogue.models';
import { catchError, map } from 'rxjs/operators';
import { of, Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CatalogueApiService {
  private readonly baseUrl = 'http://localhost:8000/api/';

  getImageUrl(image: string | null): string {
    if (!image || image === 'default.png') {
      return ''; // Ou une URL d'un avatar vide si nécessaire
    }
    return `${this.baseUrl}?action=get_image&id=${image}`;
  }

  constructor(private http: HttpClient) {}

  getCategories() {
    return this.http.get<any>(`${this.baseUrl}?action=catalogue`, { withCredentials: true })
      .pipe(map(response => response.result?.categories || []));
  }

  getCatalogue(filters: CatalogueFilters = {}) {
    let params = new HttpParams().set('action', 'catalogue');
    for (const [key, value] of Object.entries(filters)) {
      if (value !== undefined && value !== null && value !== '') {
        params = params.set(key, value);
      }
    }
    return this.http.get<any>(`${this.baseUrl}`, { params, withCredentials: true })
      .pipe(map(response => response.result?.annonces || []));
  }

  // FIX: /api/item/${id} → /api?action=item&id=${id}
  getItem(id: number) {
    return this.http.get<any>(`${this.baseUrl}?action=items&id=${id}`, { withCredentials: true })
      .pipe(map(response => response.result));
  }

  toggleFavoris(articleId: number, action: 'add' | 'remove', userId?: string) {
    if (action === 'add') {
      return this.http.post<any>(`${this.baseUrl}?action=favoris`, { article_id: articleId, user_id: userId }, { withCredentials: true });
    } else {
      return this.http.request<any>('delete', `${this.baseUrl}?action=favoris`, { body: { article_id: articleId, user_id: userId }, withCredentials: true });
    }
  }
  getUnreadCount(): Observable<number> {
    return this.http.get<any>(`${this.baseUrl}?action=unread_count`, { withCredentials: true })
      .pipe(map(response => response.result?.unread_count ?? 0));
  }
  // FIX: /api/user/${id} → /api?action=user&id=${id}
  getUser(id: string) {
    return this.http.get<any>(`${this.baseUrl}?action=user&id=${id}`, { withCredentials: true })
      .pipe(map(response => response.result));
  }

  getFavoris(): Observable<CatalogueItem[]> {
    return this.http.get<any>(`${this.baseUrl}?action=favoris`, { withCredentials: true })
      .pipe(
        map(response => {
          if (!response || !response.result) return [];
          const items = response.result.favoris ?? [];
          const images = response.result.images ?? {};
          return items.map((item: any) => ({
            ...item,
            image: images[item.id] ?? null,
            isFavoris: true
          }));
        }),
        catchError(err => {
          console.error('getFavoris error:', err.status, err.error);
          return of([]);
        })
      );
  }

  getCommandes(): Observable<any[]> {
    return this.http.get<any>(`${this.baseUrl}?action=mes_commandes`, { withCredentials: true }).pipe(
      map(response => {
        const res = response.result || {};
        const commandes = res.commandes || [];
        const images = res.images || {};
        return commandes.map((cmd: any) => ({
          ...cmd,
          image: images[cmd.article_id] || null
        }));
      })
    );
  }

  markAsReceived(venteId: number): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}?action=commande_recue`, { vente_id: venteId }, { withCredentials: true });
  }

  postReview(articleId: number, destId: number, note: number, commentaire: string): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}?action=avis`, {
      article_id: articleId,
      destinataire_id: destId,
      note,
      commentaire
    }, { withCredentials: true });
  }
  // Liste des conversations (colonne de gauche)
  getConversations(): Observable<any[]> {
    return this.http.get<any>(`${this.baseUrl}?action=conversations`, { withCredentials: true })
      .pipe(map(response => response.result || []));
  }

  // Fil complet avec un interlocuteur précis (colonne de droite)
  getMessages(withUserId: string | number): Observable<any[]> {
    return this.http.get<any>(`${this.baseUrl}?action=messages&id=${withUserId}`, { withCredentials: true })
      .pipe(map(response => response.result || []));
  }

  sendMessage(receiverId: string | number, message: string): Observable<any> {
    // 💡 On envoie 'receiverId' pour correspondre exactement à ce qu'attend le PHP
    return this.http.post<any>(
      `${this.baseUrl}?action=post_message`, 
      { receiverId, message }, 
      { withCredentials: true }
    ).pipe(map(response => response.result));
  }  
  
  uploadImage(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post<any>(`${this.baseUrl}?action=upload_image`, formData, { withCredentials: true });
  }

  postItem(formData: FormData): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}?action=post_item`, formData, { withCredentials: true })
      .pipe(map(response => response.result));
  }

  editItem(id: number, data: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}?action=edit_item&id=${id}`, data, { withCredentials: true })
      .pipe(map(response => response.result));
  }

  // FIX: id doit passer dans le body pour le DELETE (le PHP lit $inputData)
  deleteItem(id: number): Observable<any> {
    return this.http.request<any>('delete', `${this.baseUrl}?action=delete_item`, {
      body: { id },
      withCredentials: true
    });
  }

  editProfile(id: string, data: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}?action=edit_profile`, { id, ...data }, { withCredentials: true })
      .pipe(map(response => response));
  }

  adminGetUsers(): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}?action=admin_users`, { withCredentials: true });
  }

  adminGetItems(): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}?action=admin_items`, { withCredentials: true });
  }

  adminRunDashboard(): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}?action=admin_run_dashboard`, {}, { withCredentials: true });
  }

  adminDeleteUser(id: string | number): Observable<any> {
    return this.http.request<any>('delete', `${this.baseUrl}?action=delete_user`, {
      body: { id },
      withCredentials: true
    });
  }
}