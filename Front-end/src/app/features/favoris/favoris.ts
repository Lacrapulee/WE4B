import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { ArticleComponent } from '../catalogue/components/article/article.component';

@Component({
  selector: 'app-favoris',
  standalone: true,
  imports: [CommonModule, RouterModule, ArticleComponent],
  templateUrl: './favoris.html',
  styleUrls: ['./favoris.css']
})
export class FavorisComponent implements OnInit {
  items: any[] = [];
  loading = true;

  constructor(private api: CatalogueApiService) {}

  ngOnInit(): void {
    this.api.getFavoris().subscribe({
      next: (data) => {
        this.items = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur favoris:', err);
        this.loading = false;
      }
    });
  }

  onToggleFavoris(item: any) {
    this.api.toggleFavoris(item.id, 'remove').subscribe({
      next: (response) => {
        if (response.success) {
          // Remove from list or simply toggle
          this.items = this.items.filter(i => i.id !== item.id);
        }
      }
    });
  }
}
