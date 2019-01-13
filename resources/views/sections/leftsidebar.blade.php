<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="/images/users/default_user.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                @if(!empty($loggedUser))
                    <p>{{ $loggedUser->name }}</p>
                    <a href="{{ route('user.profile') }}"><i class="fa  fa-hand-o-right"></i> View Profile</a>
                @else
                    <p>Login</p>
                    <a href="{{ route('user.profile') }}"><i class="fa  fa-hand-o-right"></i> To continue</a>
                @endif
            </div>
        </div>
        @if(!empty($loggedUser))
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">MAIN NAVIGATION</li>
                <li class="{{ Request::is('dashboard')? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
                @if($loggedUser->isSuperAdmin() || $loggedUser->isAdmin() || $loggedUser->isUser())
                    <li class="treeview {{ Request::is('reports/*')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-briefcase"></i>
                            <span>Reports</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('reports/account-statement')? 'active' : '' }}">
                                <a href="{{ route('report.account-statement') }}">
                                    <i class="fa fa-circle-o text-green"></i> Account Statement
                                </a>
                            </li>
                            {{-- <li class="{{ Request::is('reports/credir-list')? 'active' : '' }}">
                                <a href="{{ route('report.credit.list') }}">
                                    <i class="fa fa-circle-o text-green"></i> Credit List
                                </a>
                            </li> --}}
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('employee-wage/*') || Request::is('employee-wage')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-user"></i>
                            <span>Employee-Wage</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('employee-wage/create')? 'active' : '' }}">
                                <a href="{{route('employee-wage.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('employee-wage')? 'active' : '' }}">
                                <a href="{{route('employee-wage.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('excavator-reading/*') || Request::is('excavator-reading')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-user"></i>
                            <span>Excavator Reading</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('excavator-reading/create')? 'active' : '' }}">
                                <a href="{{route('excavator-reading.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('excavator-reading')? 'active' : '' }}">
                                <a href="{{route('excavator-reading.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('excavator-reading/*') || Request::is('excavator-reading')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-user"></i>
                            <span>Excavator Rent</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('excavator-rent/create')? 'active' : '' }}">
                                <a href="{{route('excavator-rent.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('excavator-rent')? 'active' : '' }}">
                                <a href="{{route('excavator-rent.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('expense/*') || Request::is('expense')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-wrench"></i>
                            <span>Services & Expences</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('expense/create')? 'active' : '' }}">
                                <a href="{{route('expense.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('expense')? 'active' : '' }}">
                                <a href="{{ route('expense.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
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
                    <li class="treeview {{ Request::is('account/*') || Request::is('account') ? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-book"></i>
                            <span>Accounts</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('account/create')? 'active' : '' }}">
                                <a href="{{route('account.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('account')? 'active' : '' }}">
                                <a href="{{route('account.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('employee/*') || Request::is('employee')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-male"></i>
                            <span>Employees</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('employee/create')? 'active' : '' }}">
                                <a href="{{route('employee.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('employee')? 'active' : '' }}">
                                <a href="{{route('employee.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('excavator/*') || Request::is('excavator')? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-gavel"></i>
                            <span>Excavators</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('excavator/create')? 'active' : '' }}">
                                <a href="{{route('excavator.create') }}">
                                    <i class="fa fa-circle-o text-yellow"></i> Register
                                </a>
                            </li>
                            <li class="{{ Request::is('excavator')? 'active' : '' }}">
                                <a href="{{route('excavator.index') }}">
                                    <i class="fa fa-circle-o text-aqua"></i> List
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        @endif
    </section>
    <!-- /.sidebar -->
</aside>