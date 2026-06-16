import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FavorisButtonComponent } from '../../../../shared/ui/favoris-button/favoris-button.component';
import { CatalogueItem } from '../../../../core/models/catalogue.models';
import { CatalogueApiService } from '../../../../core/api/catalogue-api.service';

@Component({
  selector: 'app-article',
  standalone: true,
  imports: [CommonModule, RouterModule, FavorisButtonComponent],
  templateUrl: './article.component.html',
  styleUrls: ['./article.component.css']
})
export class ArticleComponent {
  @Input() item!: CatalogueItem;
  @Input() isLoggedIn: boolean = false;
  @Input() userId: number | string | null = null;
  @Output() toggleFavoris = new EventEmitter<CatalogueItem>();

  constructor(public api: CatalogueApiService) {}

  onToggleFavoris() {
    this.toggleFavoris.emit(this.item);
  }

  isOwnItem() {
    return this.userId !== null && String(this.userId) === String(this.item.vendeur_id);
  }
}