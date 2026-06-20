import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { ActivatedRoute } from '@angular/router';
import { ConversationSidebarComponent } from './components/conversation-sidebar/conversation-sidebar.component';
import { ChatWindowComponent } from './components/chat-window/chat-window.component';

@Component({
  selector: 'app-messages',
  standalone: true,
  imports: [CommonModule, RouterModule, ConversationSidebarComponent, ChatWindowComponent],
  templateUrl: './messages.html',
  styleUrls: ['./messages.css']
})
export class MessagesComponent implements OnInit {
  conversations: any[] = [];
  activeChatMessages: any[] = [];
  selectedConversation: any = null;
  loading = true;

  constructor(private api: CatalogueApiService, private route: ActivatedRoute) {}

  ngOnInit(): void {
    this.api.getConversations().subscribe({
      next: (data) => {
        this.conversations = data || [];
        this.loading = false;

        this.route.queryParams.subscribe(params => {
          const sellerId = params['contact'];
          if (sellerId) {
            const existingConv = this.conversations.find(c => c.with_user_id === sellerId);
            if (existingConv) {
              this.selectConversation(existingConv);
            } else {
              // Nouvelle discussion : on va chercher le nom du vendeur
              this.selectedConversation = { with_user_id: sellerId, last_message: "Nouvelle discussion" };
              this.activeChatMessages = [];

              this.api.getUser(sellerId).subscribe({
                next: (user) => {
                  if (this.selectedConversation && this.selectedConversation.with_user_id === sellerId) {
                    this.selectedConversation = {
                      ...this.selectedConversation,
                      nom: user?.nom ?? '',
                      prenom: user?.prenom ?? 'Utilisateur'
                    };
                  }
                },
                error: (err) => {
                  console.error('Erreur récupération du vendeur:', err);
                }
              });
            }
          }
        });
      },
      error: (err) => {
        console.error(err);
        this.loading = false;
      }
    });
  }

  selectConversation(conv: any) {
    this.selectedConversation = conv;
    this.activeChatMessages = [];
    const withUserId = conv.with_user_id;
    if (!withUserId) return;

    this.api.getMessages(withUserId).subscribe({
      next: (messages) => {
        this.activeChatMessages = messages || [];
      },
      error: (err) => {
        console.error('Erreur au chargement de l\'historique:', err);
      }
    });
  }

  backToList() {
    this.selectedConversation = null;
    this.activeChatMessages = [];
  }

  sendMessage(messageText: string) {
    if (!messageText.trim() || !this.selectedConversation) return;

    const targetUserId = this.selectedConversation.with_user_id;
    if (!targetUserId) {
      console.error("Impossible de trouver l'ID du destinataire.");
      return;
    }

    this.api.sendMessage(targetUserId, messageText).subscribe({
      next: () => {
        this.activeChatMessages = [
          ...this.activeChatMessages,
          {
            id_message: 'temp-' + Date.now(),
            content: messageText,
            date: new Date().toISOString(),
            is_me: true
          }
        ];
      },
      error: (err) => {
        console.error("Erreur d'envoi:", err);
      }
    });
  }
}