import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { AuthService } from '../../core/api/auth.service';
import { ArticleComponent } from '../catalogue/components/article/article.component';
import { ProfileCardComponent } from './components/profile-card/profile-card.component';
import { ProfileReviewsComponent } from './components/profile-reviews/profile-reviews.component';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterModule, ArticleComponent, ProfileCardComponent, ProfileReviewsComponent],
  templateUrl: './profile.html',
  styleUrls: ['./profile.css']
})
export class ProfileComponent implements OnInit {
  user: any = null;
  articles: any[] = [];
  reviews: any[] = [];
  loading = true;
  error: string | null = null;

  currentUserId: string | number | null = null;

  constructor(
    private route: ActivatedRoute,
    private api: CatalogueApiService,
    private auth: AuthService
  ) {}

  ngOnInit(): void {
    this.auth.currentUser$.subscribe(state => {
      this.currentUserId = state.user_id ?? null;
    });

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

  onUserChanged(updatedUser: any) {
    this.user = updatedUser;
  }

  isCurrentUserProfile(): boolean {
    return this.currentUserId != null && this.user != null && String(this.currentUserId) === String(this.user.id);
  }
}
