<li class="treeview {{ Request::is('voucher/*') || Request::is('voucher')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-envelope-o"></i>
                            <span>Vouchers & Reciepts</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('voucher/create')? 'active' : '' }}">
                                <a href="{{route('voucher.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('voucher')? 'active' : '' }}">
                                <a href="{{route('voucher.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>