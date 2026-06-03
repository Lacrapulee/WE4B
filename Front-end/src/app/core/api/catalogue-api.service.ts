import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../models/catalogue.models';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class CatalogueApiService {
  private readonly baseUrl = 'http://localhost:8000';

  constructor(private http: HttpClient) {}

  getCategories() {
    return this.http.get<{categories: CatalogueCategory[]}>(`${this.baseUrl}/routeur.php?action=catalogue`)
      .pipe(map(response => response.categories));
  }

  getCatalogue(filters: CatalogueFilters = {}) {
    let params = new HttpParams().set('action', 'catalogue');

    for (const [key, value] of Object.entries(filters)) {
      if (value !== undefined && value !== null && value !== '') {
        params = params.set(key, value);
      }
    }

    return this.http.get<{articles: CatalogueItem[]}>(`${this.baseUrl}/routeur.php`, { params })
      .pipe(map(response => response.articles));
  }

  getItem(id: number) {
    return this.http.get<CatalogueItem>(`${this.baseUrl}/routeur.php`, { params: { action: 'item', id: id.toString() } });
  }

  toggleFavoris(articleId: number) {
    return this.http.post<{ success: boolean }>(`${this.baseUrl}/routeur.php?action=favoris_ajax`, { action: 'add', article_id: articleId });
  }
}