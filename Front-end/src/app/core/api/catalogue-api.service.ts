import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../models/catalogue.models';
import { catchError, map } from 'rxjs/operators';
import { of, Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CatalogueApiService {
  private readonly baseUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getCategories() {
    return this.http.get<{categories: CatalogueCategory[]}>(`${this.baseUrl}/catalogue`, { withCredentials: true }) // Update endpoint correctly based on backend
      .pipe(map(response => response.categories || []));
  }

  getCatalogue(filters: CatalogueFilters = {}) {
    let params = new HttpParams();
    for (const [key, value] of Object.entries(filters)) {
      if (value !== undefined && value !== null && value !== '') {
        params = params.set(key, value);
      }
    }
    return this.http.get<{articles: CatalogueItem[]}>(`${this.baseUrl}/catalogue`, { params, withCredentials: true })
      .pipe(map(response => response.articles || []));
  }

  getItem(id: number) {
    return this.http.get<any>(`${this.baseUrl}/item/${id}`, { withCredentials: true });
  }

  toggleFavoris(articleId: number, action: 'add' | 'remove', userId?: string) {
    if (action === 'add') {
      return this.http.post<{ success: boolean }>(`${this.baseUrl}/favoris`, { article_id: articleId, user_id: userId }, { withCredentials: true });
    } else {
      return this.http.request<{ success: boolean }>('delete', `${this.baseUrl}/favoris`, { body: { article_id: articleId, user_id: userId }, withCredentials: true });
    }
  }

  getUser(id: string) {
    return this.http.get<any>(`${this.baseUrl}/user/${id}`, { withCredentials: true });
  }

  getFavoris(): Observable<CatalogueItem[]> {
  return this.http.get<any>(`${this.baseUrl}/favoris`, { withCredentials: true })
    .pipe(
      map(response => {
        if (!response) return [];
        const items = response.favoris ?? [];
        const images = response.images ?? {};
        return items.map((item: any) => ({
          ...item,
          image: images[item.id] ?? 'default.png',
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
    // Mocking for now
    return of([]);
  }

  getMessages(): Observable<any[]> {
    // Mocking for now
    return of([]);
  }

  postItem(formData: FormData): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}/post_item`, formData, { withCredentials: true });
  }

  editItem(id: number, data: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}/edit_item?id=${id}`, data, { withCredentials: true });
  }

  deleteItem(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/delete_item?id=${id}`, { withCredentials: true });
  }
}