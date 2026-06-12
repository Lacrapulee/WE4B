import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../models/catalogue.models';
import { map } from 'rxjs/operators';
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

  toggleFavoris(articleId: number) {
    return this.http.post<{ success: boolean }>(`${this.baseUrl}/favoris/toggle`, { article_id: articleId, action: 'add' }, { withCredentials: true });
  }

  getUser(id: string) {
    return this.http.get<any>(`${this.baseUrl}/user/${id}`, { withCredentials: true });
  }

  getFavoris(): Observable<CatalogueItem[]> {
    // Mocking for now as endpoint doesn't seem explicitly available in GET API
    return of([]);
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
}