const app = Vue.createApp({
    data() {
        return {
            products: [],
            currentPage:1,
            lastPage:1,
            sortBy: 'asc',
            orderBy: 'name',
            searchQuery: '',
            buttonIsLoading: false,
            addedItem: 0,
            activeTab: 'home',
            numberOfItemsInCart:0,
            cart: [],
            order: {},
            custumerEmail:'',
            stripeKey: '',
            stripe:{},
            cardElement:{},
            pageIsLoading: true
        }
    },
    watch: {
        currentPage(newValue, oldValue) {
            if(newValue <= this.lastPage && newValue > 0) {
                this.fetchProducts();
            }

        },
        sortBy() {
            this.fetchProducts();
        },
        orderBy() {
            this.fetchProducts();
        },
        searchQuery() {
            this.fetchProducts();
        },
    },
    mounted() {

        this.stripeKey = document.getElementById('stripe_key').value;
        this.fetchProducts();
        if (localStorage.getItem("order_id") != null) {
            this.orderId = localStorage.getItem("order_id");
            this.getOrderDetails();
        }
        this.initStripe();
    },
    methods : {
        setActiveTab (tab) {
            this.activeTab = tab;
        },
        fetchProducts () {
            let url = `/products?page=${this.currentPage}&search_query=${this.searchQuery}&order_by=${this.orderBy}&sort_by=${this.sortBy}`;
            axios
                .get(url)
                .then((response) => {
                    this.products = response.data.products.data;
                    this.currentPage = response.data.products.current_page;
                    this.lastPage = response.data.products.last_page;
                    this.pageIsLoading = false;
                })
                .catch((err) => {
                    this.pageIsLoading = false;
                    this.$notify.error({
                        title: "Error",
                        message: "Unable to fetch products",
                    });
                });
        },
        goToPrevPage() {
            if(this.currentPage > 1){
                this.currentPage -= 1;
            }
        },
        goToNextPage() {

            if(this.currentPage < this.lastPage){
                this.currentPage += 1;
            }
        },
        addItemToCart(productId){
            this.buttonIsLoading = true;
            this.addedItem = productId;
            let url = ''
            if (localStorage.getItem("order_id") === null) {
                this.orderId = Math.round(new Date().getTime() / 1000).toString();
                localStorage.setItem("order_id", this.orderId);
                url = `/orders`;
            }else{
                this.orderId = localStorage.getItem("order_id");
                url = `/orders/${this.orderId}/items`;
            }
            let order = {
                order_id: this.orderId,
                item: {
                    product_id: this.addedItem,
                    quantity:1
                }
            }
            this.storeOrder(url, order);
        },
        storeOrder(url, order) {
            axios
            .post(url,order)
            .then((response) => {
                this.cart = response.data.order.parsed_cart;
                this.order = response.data.order;
                this.numberOfItemsInCart = this.cart.length;

                this.buttonIsLoading = false;
                this.addedItem = 0;
                this.$notify.success({
                    title: "Success",
                    message: "Item added to cart",
                });
            })
            .catch((err) => {
                this.buttonIsLoading = false;
                this.addedItem = 0;
                this.$notify.error({
                    title: "Error",
                    message: "Unable to store order",
                });
            });
        },
        getOrderDetails() {
            let url = `/orders/${this.orderId}`;
            axios
                .get(url)
                .then((response) => {
                    this.order = response.data.order;
                    this.cart = response.data.order.parsed_cart;
                    this.numberOfItemsInCart = this.cart.length;
                })
                .catch((err) => {
                    console.error(err);
                });
        },
        deletItemFromOrder(productId) {
            let url = `/orders/${this.orderId}/items`;
            axios
                .delete(url, {data:{item:{product_id: productId}}})
                .then((response) => {
                    this.order = response.data.order;
                    this.cart = response.data.order.parsed_cart;
                    this.numberOfItemsInCart = this.cart.length;
                    this.$notify.success({
                        title: "Success",
                        message: response.data.message,
                    });
                })
                .catch((err) => {
                    this.$notify.error({
                        title: "Error",
                        message: "Unable to delete item from order",
                    });
                });
        },

        initStripe() {
            this.stripe = Stripe(this.stripeKey);
            var elements = this.stripe.elements();

            var style = {
                base: {
                    color: "#32325d",
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: "antialiased",
                    fontSize: "16px",
                    "::placeholder": {
                        color: "#aab7c4"
                    }
                },
                invalid: {
                    color: "#fa755a",
                    iconColor: "#fa755a"
                }
            };

            this.cardElement = elements.create("card", { style: style ,hidePostalCode: true});
            this.cardElement.mount("#card-element");

            this.cardElement.on('change', this.showCardError);
        },

        createPaymentMethod() {
            if(!this.orderId || this.numberOfItemsInCart < 1) {
                this.$notify.error({
                    title: "Error",
                    message: "No order found, Add an Item to your cart to complete payment",
                });

                return;
            }
            if(this.custumerEmail == "") {
                this.$notify.error({
                    title: "Error",
                    message: "No email found, Add an email to complete payment",
                });

                return;
            }
            this.stripe
                .createToken(this.cardElement).then((result) => {

                    if (result.error) {
                        // Inform the customer that there was an error.
                        this.$notify.error({
                            title: "Error",
                            message: "Unable to complete transaction, Encountered an error",
                        });
                      } else {
                        // Send the token to your server.
                        this.stripeInitiateTransaction(result.token);
                      }
                })
        },
        showCardError(event) {
            let displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        },
        stripeInitiateTransaction(token) {
            let url = `/orders/${this.orderId}/payment`;
            axios
                .post(url,{stripe_token: token, email: this.custumerEmail})
                .then((response) => {
                    localStorage.clear();
                    this.orderId = "";
                    this.cart = [];
                    this.order = {};
                    this.numberOfItemsInCart = 0;
                    this.activeTab = 'home';

                    this.$notify.success({
                        title: "Success",
                        message: "Payment completed",
                    });
                })
                .catch((err) => {
                    this.$notify.error({
                        title: "Error",
                        message: "Unable to complete payment",
                    });
                });
        }
    }
});
app.use(ElementPlus);
app.mount('#mini-cart');