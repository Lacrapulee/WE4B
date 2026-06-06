import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';

@Component({
  selector: 'app-messages',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './messages.html',
  styleUrls: ['./messages.css']
})
export class MessagesComponent implements OnInit {
  conversations: any[] = [];
  selectedConversation: any = null;
  loading = true;
  newMessage = '';

  constructor(private api: CatalogueApiService) {}

  ngOnInit(): void {
    this.api.getMessages().subscribe({
      next: (data) => {
        this.conversations = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur messages:', err);
        this.loading = false;
      }
    });
  }

  selectConversation(conv: any) {
    this.selectedConversation = conv;
  }

  sendMessage() {
    if (!this.newMessage.trim()) return;
    
    // In a real scenario, call API here
    if (this.selectedConversation) {
      if (!this.selectedConversation.messages) {
        this.selectedConversation.messages = [];
      }
      this.selectedConversation.messages.push({
        id: Date.now(),
        sender_id: 'me', // Mocking current user ID
        text: this.newMessage,
        created_at: new Date()
      });
      this.newMessage = '';
    }
  }
}
