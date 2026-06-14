import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../core/api/auth.service';

@Component({
  selector: 'app-inscription',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './inscription.html',
  styleUrl: './inscription.css',
})
export class Inscription {
  formData = {
    email: '',
    password: '',
    confirm_password: '',
    prenom: '',
    nom: '',
    telephone: '',
    date_naissance: '',
    adresse_postale: ''
  };

  isLoading = false;
  message = '';
  isSuccess = false;

  constructor(private router: Router, private authService: AuthService) {}

  onSubmit() {
    this.isLoading = true;
    this.message = '';

    if (this.formData.password !== this.formData.confirm_password) {
      this.isSuccess = false;
      this.message = 'Les mots de passe ne correspondent pas.';
      this.isLoading = false;
      return;
    }

    if (!this.formData.email || !this.formData.password) {
      this.isSuccess = false;
      this.message = 'Veuillez remplir les champs obligatoires (Email, Mot de passe).';
      this.isLoading = false;
      return;
    }

    this.authService.register(this.formData).subscribe({
      next: (response) => {
        if (response.result) {
          this.isSuccess = true;
          this.message = response.message || 'Inscription réussie ! Redirection...';
          setTimeout(() => {
            this.isLoading = false;
            this.router.navigate(['/']);
          }, 1500);
        } else {
          this.isSuccess = false;
          this.isLoading = false;
          this.message = response.message || response.error || 'Erreur lors de l\'inscription.';
        }
      },
      error: (err) => {
        this.isSuccess = false;
        this.isLoading = false;
        this.message = err.error?.error || err.error?.message || 'Une erreur est survenue. Veuillez réessayer.';
        console.error('Registration error', err);
      }
    });
  }
}