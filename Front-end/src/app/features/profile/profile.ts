import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { AuthService } from '../../core/api/auth.service';
import { ArticleComponent } from '../catalogue/components/article/article.component';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, ArticleComponent],
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
  isEditing: boolean = false;
  editData: any = {};
  saveError: string | null = null;
  saveSuccess: string | null = null;

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
        
        // Prepare edit data
        if (this.user) {
          this.editData = {
            nom: this.user.nom,
            prenom: this.user.prenom,
            email: this.user.email,
            telephone: this.user.telephone,
            adresse_postale: this.user.adresse_postale || ''
          };
        }
      },
      error: (err) => {
        console.error('Erreur profil:', err);
        this.error = 'Impossible de charger le profil.';
        this.loading = false;
      }
    });
  }

  toggleEdit() {
    this.isEditing = !this.isEditing;
    this.saveError = null;
    this.saveSuccess = null;
    if (!this.isEditing && this.user) {
      // Reset data if cancelled
      this.editData = {
        nom: this.user.nom,
        prenom: this.user.prenom,
        email: this.user.email,
        telephone: this.user.telephone,
        adresse_postale: this.user.adresse_postale || ''
      };
    }
  }

  saveProfile() {
    this.saveError = null;
    this.saveSuccess = null;
    
    if (!this.editData.nom || !this.editData.prenom) {
      this.saveError = "Le nom et le prénom sont obligatoires.";
      return;
    }

    this.api.editProfile(this.user.id, this.editData).subscribe({
      next: (res) => {
        if (res.success || res.message === 'Profil mis à jour avec succès') {
          this.saveSuccess = "Profil mis à jour avec succès.";
          this.user = { ...this.user, ...this.editData };
          this.isEditing = false;
        } else {
          this.saveError = res.error || res.message || "Erreur lors de la mise à jour.";
        }
      },
      error: (err) => {
        console.error('Erreur saveProfile:', err);
        this.saveError = "Une erreur est survenue lors de la sauvegarde.";
      }
    });
  }

  isCurrentUserProfile(): boolean {
    return this.currentUserId != null && this.user != null && String(this.currentUserId) === String(this.user.id);
  }
}
