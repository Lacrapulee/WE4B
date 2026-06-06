import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { ArticleComponent } from '../catalogue/components/article/article.component';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterModule, ArticleComponent],
  templateUrl: './profile.html',
  styleUrls: ['./profile.css']
})
export class ProfileComponent implements OnInit {
  user: any = null;
  articles: any[] = [];
  reviews: any[] = [];
  loading = true;
  error: string | null = null;

  constructor(
    private route: ActivatedRoute,
    private api: CatalogueApiService
  ) {}

  ngOnInit(): void {
    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.loadUser(id);
      }
    });
  }

  loadUser(id: string) {
    this.loading = true;
    this.api.getUser(id).subscribe({
      next: (data) => {
        this.user = data.user;
        this.articles = data.articles || [];
        this.reviews = data.reviews || [];
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur profil:', err);
        this.error = 'Impossible de charger le profil.';
        this.loading = false;
      }
    });
  }
}
