import { Component, Input, Output, EventEmitter, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CatalogueApiService } from '../../../../core/api/catalogue-api.service';

@Component({
  selector: 'app-profile-card',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './profile-card.component.html'
})
export class ProfileCardComponent implements OnChanges {
  @Input() user: any = null;
  @Input() isCurrentUser: boolean = false;
  @Output() userChanged = new EventEmitter<any>();

  isEditing: boolean = false;
  editData: any = {};
  saveError: string | null = null;
  saveSuccess: string | null = null;

  constructor(private api: CatalogueApiService) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['user'] && this.user) {
      this.resetEditData();
    }
  }

  resetEditData() {
    this.editData = {
      nom: this.user.nom,
      prenom: this.user.prenom,
      email: this.user.email,
      telephone: this.user.telephone,
      adresse_postale: this.user.adresse_postale || ''
    };
  }

  toggleEdit() {
    this.isEditing = !this.isEditing;
    this.saveError = null;
    this.saveSuccess = null;
    if (!this.isEditing && this.user) {
      this.resetEditData();
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
          const updatedUser = { ...this.user, ...this.editData };
          this.userChanged.emit(updatedUser);
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
}
