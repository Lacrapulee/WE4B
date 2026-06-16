import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-commandes',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './commandes.html',
  styleUrls: ['./commandes.css']
})
export class CommandesComponent implements OnInit {
  commandes: any[] = [];
  loading = true;

  reviewingCmd: any = null;
  reviewNote: number = 5;
  reviewComment: string = '';
  submittingReview = false;
  reviewError: string | null = null;

  constructor(public api: CatalogueApiService) {}

  ngOnInit(): void {
    this.loadCommandes();
  }

  loadCommandes() {
    this.loading = true;
    this.api.getCommandes().subscribe({
      next: (data) => {
        this.commandes = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur commandes:', err);
        this.loading = false;
      }
    });
  }

  markAsReceived(cmd: any) {
    if (!confirm('Confirmez-vous la réception de cette commande ?')) return;
    this.api.markAsReceived(cmd.id).subscribe({
      next: (res) => {
        if (res.success) {
          cmd.statut = 'recu';
        }
      },
      error: (err) => console.error(err)
    });
  }

  openReviewModal(cmd: any) {
    this.reviewingCmd = cmd;
    this.reviewNote = 5;
    this.reviewComment = '';
    this.reviewError = null;
  }

  cancelReview() {
    this.reviewingCmd = null;
  }

  submitReview() {
    if (!this.reviewingCmd || !this.reviewComment.trim()) {
      this.reviewError = "Veuillez entrer un commentaire.";
      return;
    }
    
    this.submittingReview = true;
    this.reviewError = null;
    
    this.api.postReview(
      this.reviewingCmd.article_id, 
      this.reviewingCmd.vendeur_id, 
      this.reviewNote, 
      this.reviewComment
    ).subscribe({
      next: (res) => {
        this.submittingReview = false;
        if (res.success) {
          alert('Avis laissé avec succès !');
          this.reviewingCmd.a_laisse_avis = 1;
          this.reviewingCmd = null;
        } else {
          this.reviewError = res.error || "Erreur lors de l'envoi de l'avis.";
        }
      },
      error: (err) => {
        this.submittingReview = false;
        this.reviewError = err.error?.error || "Erreur serveur.";
      }
    });
  }
}
